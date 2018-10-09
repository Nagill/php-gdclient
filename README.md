# php gd库操纵lib

# Basic

```
$gdclient = new Gdclient();
$gdclient->imageCreate('test.img')
$gdclient->imageTextXCenter($size, $angle, $y, $font, $text, $color);
$gdclient->ouputFile("build.png","png");
```