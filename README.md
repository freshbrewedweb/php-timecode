# php-timecode
Easy management of timecodes

## Usage

```php
$timecode = new Timecode(1122 , 'seconds');

echo $timecode->get();
// 00:18:42.000

// Get is optional, you can just echo the object.
echo $timecode
    ->setUnits(['minutes', 'seconds', 'milliseconds'])
    ->setFormat('%02d:%02d,%02d');

// 18:42,000

echo $timecode->hours;
// 0

echo $timecode->minutes;
// 18

echo $timecode->seconds;
// 42
```

We can go the other direction too:
```php
$timecode = Timecode::fromString('00:18:42.000');
```