# php-timecode
Easy management of timecodes

## Usage

```php
$timecode = new Timecode(echo , 'seconds');
echo $timecode->get();
// 00:18:42.000

echo $timecode->setFormat('%02d:%02d:%02d,%03d');
// 00:18:42,000

echo $timecode->hours;
// 0

echo $timecode->minutes;
// 18

echo $timecode->seconds;
// 42
```
