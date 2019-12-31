<?php

namespace Freshbrewedweb;

class TimeCode {

    const UNIT_FACTORS = [
        'nanoseconds' => 1,
        'microseconds' => 1000,
        'milliseconds' => 1000,
        'seconds' => 1000,
        'minutes' => 60,
        'hours' => 60,
        'days' => 24,
        'weeks' => 7,
    ];

    private $vocabulary = [
        'nanoseconds' => ['nanosecond', 'ns', 'nanoseconds'],
        'microseconds' => ['microsecond', 'μs', 'microseconds'],
        'milliseconds' => ['millisecond', 'ms', 'milliseconds'],
        'seconds' => ['second', 's', 'seconds'],
        'minutes' => ['minute', 'min', 'minutes'],
        'hours' => ['hour', 'h', 'hours'],
        'days' => ['day', 'd', 'days'],
        'weeks' => ['week', 'w', 'weeks'],
    ];

    protected $string;

    /**
     * Converted to nanoseconds since
     * it's the atomic unit.
     */
    protected $time = 0;
    protected $unit;

    protected $format = '%02d:%02d:%02d.%03d';
    protected $formatUnits = ['hours', 'minutes', 'seconds', 'milliseconds'];

    /**
     * Timecode units
     */
    protected $nanoseconds;
    protected $microseconds;
    protected $milliseconds;
    protected $seconds;
    protected $hours;
    protected $days;
    protected $weeks;

    public function __construct( int $time = 0, string $unit = 'milliseconds' )
    {
        $this->unit = $this->sanitizeUnit( $unit );
        $this->atomicTime($time);
        $this->setTimecodes();
    }

    public function __get( $unit )
    {
        return $this->{$unit};
    }

    public function __toString()
    {
        return $this->get();
    }

    /**
     * Converts the time given to 
     * nanoseconds, since this is our 
     * atomic unit of time.
     */
    public function atomicTime( $time = null )
    {
        $ns = 0;

        if( is_null($time) ) {
            foreach (array_keys(self::UNIT_FACTORS) as $unit) {
                $ns = $ns + $this->toNanoseconds($this->{$unit}, $unit);
            }
        } else {
            $ns = $this->toNanoseconds($time, $this->unit);
        }

        $this->time = $ns;
    }

    public function setUnits( array $units )
    {
        $this->formatUnits = $units;
        return $this;
    }

    public function setFormat( string $format )
    {
        $this->format = $format;
        return $this;
    }

    public function set( $property, $value )
    {
        $this->{$property} = $value;
    }

    public function get()
    {
        $args = [$this->format];
        foreach( $this->formatUnits as $unit ) {
            $args[] = $this->{$unit};
        }

        return call_user_func_array('sprintf', $args);
    }

    public function timeIn( $unit )
    {
        $t = $this->time;
        foreach(self::UNIT_FACTORS as $u => $factor) {
            $t = $t / $factor;
            if($u == $unit) {
                break;
            }
        }
        return $t;
    }

    private function toNanoseconds( $t, $u ) {
        foreach( self::UNIT_FACTORS as $unit => $factor ) {
            $t = $t * $factor;
            if( $u == $unit )
                break;
        }
        return $t;
    }

    private function timeToUnit( string $unit )
    {
        $units = self::UNIT_FACTORS;
        $factor = 1;
        foreach( $units as $u => $f ) {
            $factor = $factor * $f;
            if( $unit == $u )
                break;
        }
        return ($this->time / $factor);
    }

    private function setString( $string )
    {
        $this->string = $string;
    }

    private function setTimecodes()
    {
        $units = self::UNIT_FACTORS;
        foreach( $units as $unit => $factor ) {
            if( $nf = next($units) ) {
                $this->{$unit} = $this->timeToUnit($unit) % $nf;
            } else {
                $this->{$unit} = $this->timeToUnit($unit);
            }
        }
    }

    private function sanitizeUnit( $unit )
    {
        $unit = strtolower($unit);
        foreach( $this->vocabulary as $defactoUnit => $variations ) {
            if( in_array( $unit, $variations ) ) {
                return $defactoUnit;
            }
        }

        throw new \Exception(
            sprintf(
                "'%s' is not an acceptable unit. Choose from: %s", $unit,
                implode(', ', array_keys(self::UNIT_FACTORS))
            )
        );
    }

    public static function fromString( $string, $units, $format )
    {
        $n = sscanf($string, $format);
        if( count($n) != count($units) ) {
            throw new \Exception(sprintf(
                'Array length of format string (%d) must be the same as the units provided (%d)', count($n), count($units)
            ));
        }

        $units = array_combine($units, $n);
        $timecode = new self();
        foreach( $units as $unit => $value ) {
            $timecode->set($unit, $value);
        }

        $timecode->atomicTime();
        return $timecode;
    }
}