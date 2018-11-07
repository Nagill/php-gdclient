<?php

class Gdclient {
    protected $imageResource;
    protected $resourceList = array();
    protected $allow_ext = array('jpg', 'jpeg', 'png');
    public function __construct() {
    }
    
    public function createEmptyImage($width, $height, $backgroundColor, $alpha = 0) {
        if ($alpha < 0 || $alpha > 127) {
            throw new Exception("alpha range error!", 1);
        }
        
        $this->imageResource = imagecreatetruecolor($width, $height);
        $bg                  = $this->colorallocate($backgroundColor, $alpha);
        imagefill($this->imageResource, 0, 0, $bg);
    }
    
    public function imageCreate($src) {
        if (empty($src)) {
            throw new Exception("image src is empty");
        }
        
        if (!is_file($src)) {
            throw  new Exception('file is not exist');
        }
        
        $this->imageResource = $this->getHandler($src);
    }
 
    public function imageText($size, $angle, $x, $y, $font, $text, $color, $alpha = 0) {
        $c   = $this->colorallocate($color, $alpha);
        $box = imageftbbox($size, $angle, $font, $text);
        // 通过imageftbbox 获取文本的高度，然后y坐标减去高度即可获取到文字的y点坐标
        imagettftext($this->imageResource, $size, $angle, $x, $y - $box[7], $c, $font, $text);
    }
    
    public function imageTextXCenter($size, $angle, $y, $font, $text, $color, $alpha = 0) {
        $c     = $this->colorallocate($color, $alpha);
        $box   = imageftbbox($size, $angle, $font, $text);
        $width = imagesx($this->imageResource);
        // 通过imageftbbox 获取文本的高度，然后y坐标减去高度即可获取到文字的y点坐标
        imagettftext($this->imageResource, $size, $angle, ceil(($width - $box[2]) / 2), $y - $box[7], $c, $font, $text);
    }
    
    public function imageTextYCenter($size, $angle, $x, $font, $text, $color, $alpha = 0) {
        $c      = $this->colorallocate($color, $alpha);
        $box    = imageftbbox($size, $angle, $font, $text);
        $height = imagesy($this->imageResource);
        // 通过imageftbbox 获取文本的高度，然后y坐标减去高度即可获取到文字的y点坐标
        $y = ceil($height) / 2 - ($box[7] - $box[1]) / 2;
        imagettftext($this->imageResource, $size, $angle, $x, $y, $c, $font, $text);
    }
    
    public function imagecopy($src, $dx, $dy, $sx, $sy, $sw, $sh, $blur = FALSE, $rotate = 0, $roatteColor = "") {
        $src = $this->getHandler($src);
        if ($rotate != 0) {
            $src = imagerotate($src, $rotate, $roatteColor);
        }
        
        if ($blur) {
            $src = $this->blur($src);
        }
        
        imagecopy($this->imageResource, $src, $dx, $dy, $sx, $sy, $sw * imagesx($src), $sh * imagesy($src));
    }
    
    public function imagecopymerge($src, $dx, $dy, $sx, $sy, $sw, $sh, $pct = 100, $blur = FALSE, $rotate = 0, $roatteColor = 0xc5b9b2) {
        $src = $this->getHandler($src);
        if ($rotate != 0) {
            $src = imagerotate($src, $rotate, $roatteColor);
        }
        
        if ($blur) {
            $src = $this->blur($src);
        }
        imagecopymerge($this->imageResource, $src, $dx, $dy, $sx, $sy, $sw * imagesx($src), $sh * imagesy($src), $pct);
    }
    
    public function thumb($width, $height, $type = 1) {
        $w = $this->getWidth();
        $h = $this->getHeight();
        
        if ($type == 1) {
            $img = imagecreatetruecolor($width, $height);
            imagecopyresampled($img, $this->imageResource, 0, 0, 0, 0, $width, $height, $w, $h);
        } else {
            $img = imagecreatetruecolor($width, $width * $height / $w);
            imagecopyresampled($img, $this->imageResource, 0, 0, 0, 0, $width, $width * $height / $w, $w, $h);
        }
        
        $this->imageResource = $img;
    }
    
    function radius($radius) {
        $w   = $this->getWidth();
        $h   = $this->getHeight();
        $img = imagecreatetruecolor($w, $h);
        imagesavealpha($img, TRUE);
        //拾取一个完全透明的颜色,最后一个参数127为全透明
        $bg = imagecolorallocatealpha($img, 255, 255, 255, 127);
        imagefill($img, 0, 0, $bg);
        $r = $radius; //圆 角半径
        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                $rgbColor = imagecolorat($this->imageResource, $x, $y);
                if (($x >= $radius && $x <= ($w - $radius)) || ($y >= $radius && $y <= ($h - $radius))) {
                    //不在四角的范围内,直接画
                    imagesetpixel($img, $x, $y, $rgbColor);
                } else {
                    //在四角的范围内选择画
                    //上左
                    $y_x = $r; //圆心X坐标
                    $y_y = $r; //圆心Y坐标
                    if (((($x - $y_x) * ($x - $y_x) + ($y - $y_y) * ($y - $y_y)) <= ($r * $r))) {
                        imagesetpixel($img, $x, $y, $rgbColor);
                    }
                    //上右
                    $y_x = $w - $r; //圆心X坐标
                    $y_y = $r; //圆心Y坐标
                    if (((($x - $y_x) * ($x - $y_x) + ($y - $y_y) * ($y - $y_y)) <= ($r * $r))) {
                        imagesetpixel($img, $x, $y, $rgbColor);
                    }
                    //下左
                    $y_x = $r; //圆心X坐标
                    $y_y = $h - $r; //圆心Y坐标
                    if (((($x - $y_x) * ($x - $y_x) + ($y - $y_y) * ($y - $y_y)) <= ($r * $r))) {
                        imagesetpixel($img, $x, $y, $rgbColor);
                    }
                    //下右
                    $y_x = $w - $r; //圆心X坐标
                    $y_y = $h - $r; //圆心Y坐标
                    if (((($x - $y_x) * ($x - $y_x) + ($y - $y_y) * ($y - $y_y)) <= ($r * $r))) {
                        imagesetpixel($img, $x, $y, $rgbColor);
                    }
                }
            }
        }
        imagedestroy($this->imageResource);
        $this->imageResource = $img;
        
    }
    /**
    *图片居中裁剪
    */
    public function image_center_crop($width, $height) {
        /* 获取图像尺寸信息 */
        $target_w = $width;
        $target_h = $height;
        $source_w = $this->getWidth();
        $source_h = $this->getHeight();
        /* 计算裁剪宽度和高度 */
        $judge    = (($source_w / $source_h) > ($target_w / $target_h));
        $resize_w = $judge ? ($source_w * $target_h) / $source_h : $target_w;
        $resize_h = !$judge ? ($source_h * $target_w) / $source_w : $target_h;
        $start_x  = $judge ? ($resize_w - $target_w) / 2 : 0;
        $start_y  = !$judge ? ($resize_h - $target_h) / 2 : 0;
        /* 绘制居中缩放图像 */
        $resize_img = imagecreatetruecolor($resize_w, $resize_h);
        imagecopyresampled($resize_img, $this->imageResource, 0, 0, 0, 0, $resize_w, $resize_h, $source_w, $source_h);
        $target_img = imagecreatetruecolor($target_w, $target_h);
        imagecopy($target_img, $resize_img, 0, 0, $start_x, $start_y, $resize_w, $resize_h);

        $this->imageResource = $target_img;
    }
    
    /**
     * 处理成圆形图片
     */
    public function circle(){
        $w   = $this->getWidth();
        $h   = $this->getHeight();
        $w   = min($w, $h);
        $h   = $w;
        $img = imagecreatetruecolor($w, $h);
        imagesavealpha($img, true);
        //拾取一个完全透明的颜色,最后一个参数127为全透明
        $bg = imagecolorallocatealpha($img, 255, 255, 255, 127);
        imagefill($img, 0, 0, $bg);
        $r   = $w / 2; //圆半径
        for ($x = 0; $x < $w; $x++) {
            for ($y = 0; $y < $h; $y++) {
                $rgbColor = imagecolorat($this->imageResource, $x, $y);
                if (((($x - $r) * ($x - $r) + ($y - $r) * ($y - $r)) < ($r * $r))) {
                    imagesetpixel($img, $x, $y, $rgbColor);
                }
            }
        }
        
        imagedestroy($this->imageResource);
        $this->imageResource = $img;
    }
    
    /**
     * 高斯模糊，针对底图的高斯模糊
     *
     * @param int $blurFactor
     */
    public function pblur($blurFactor = 3) {
        // blurFactor has to be an integer
        $blurFactor = round($blurFactor);
        
        $originalWidth  = imagesx($this->imageResource);
        $originalHeight = imagesy($this->imageResource);
        
        $smallestWidth  = ceil($originalWidth * pow(0.5, $blurFactor));
        $smallestHeight = ceil($originalHeight * pow(0.5, $blurFactor));
        
        // for the first run, the previous image is the original input
        $prevImage  = $this->imageResource;
        $prevWidth  = $originalWidth;
        $prevHeight = $originalHeight;
        
        // scale way down and gradually scale back up, blurring all the way
        for ($i = 0; $i < $blurFactor; $i += 1) {
            // determine dimensions of next image
            $nextWidth  = $smallestWidth * pow(2, $i);
            $nextHeight = $smallestHeight * pow(2, $i);
            
            // resize previous image to next size
            $nextImage = imagecreatetruecolor($nextWidth, $nextHeight);
            imagecopyresized(
                $nextImage,
                $prevImage,
                0,
                0,
                0,
                0,
                $nextWidth,
                $nextHeight,
                $prevWidth,
                $prevHeight
            );
            
            // apply blur filter
            imagefilter($nextImage, IMG_FILTER_GAUSSIAN_BLUR);
            
            // now the new image becomes the previous image for the next step
            $prevImage  = $nextImage;
            $prevWidth  = $nextWidth;
            $prevHeight = $nextHeight;
        }
        
        // scale back to original size and blur one more time
        imagecopyresized(
            $this->imageResource,
            $nextImage,
            0,
            0,
            0,
            0,
            $originalWidth,
            $originalHeight,
            $nextWidth,
            $nextHeight
        );
        imagefilter($this->imageResource, IMG_FILTER_GAUSSIAN_BLUR);
        
        // clean up
        imagedestroy($prevImage);
    }
    
    /**
     * 图片高斯模糊，针对底图上的水印图片
     *
     * @param     $gdImageResource
     * @param int $blurFactor
     *
     * @return mixed
     */
    private function blur($gdImageResource, $blurFactor = 3) {
        // blurFactor has to be an integer
        $blurFactor = round($blurFactor);
        
        $originalWidth  = imagesx($gdImageResource);
        $originalHeight = imagesy($gdImageResource);
        
        $smallestWidth  = ceil($originalWidth * pow(0.5, $blurFactor));
        $smallestHeight = ceil($originalHeight * pow(0.5, $blurFactor));
        
        // for the first run, the previous image is the original input
        $prevImage  = $gdImageResource;
        $prevWidth  = $originalWidth;
        $prevHeight = $originalHeight;
        
        // scale way down and gradually scale back up, blurring all the way
        for ($i = 0; $i < $blurFactor; $i += 1) {
            // determine dimensions of next image
            $nextWidth  = $smallestWidth * pow(2, $i);
            $nextHeight = $smallestHeight * pow(2, $i);
            
            // resize previous image to next size
            $nextImage = imagecreatetruecolor($nextWidth, $nextHeight);
            imagecopyresized(
                $nextImage,
                $prevImage,
                0,
                0,
                0,
                0,
                $nextWidth,
                $nextHeight,
                $prevWidth,
                $prevHeight
            );
            
            // apply blur filter
            imagefilter($nextImage, IMG_FILTER_GAUSSIAN_BLUR);
            
            // now the new image becomes the previous image for the next step
            $prevImage  = $nextImage;
            $prevWidth  = $nextWidth;
            $prevHeight = $nextHeight;
        }
        
        // scale back to original size and blur one more time
        imagecopyresized(
            $gdImageResource,
            $nextImage,
            0,
            0,
            0,
            0,
            $originalWidth,
            $originalHeight,
            $nextWidth,
            $nextHeight
        );
        imagefilter($gdImageResource, IMG_FILTER_GAUSSIAN_BLUR);
        
        // clean up
        imagedestroy($prevImage);
        
        // return result
        return $gdImageResource;
    }
    
    /**
     * 输出并释放资源
     *
     * @param string $type
     */
    public function outputBower($type = 'jpeg') {
        if ($type == 'jpeg' || $type == 'jpg') {
            header('Content-type: image/jpeg');
            imagejpeg($this->imageResource);
        } else {
            header('Content-type: image/png');
            imagesavealpha($this->imageResource, TRUE);
            imagepng($this->imageResource);
        }
        $this->destory();
    }
    
    /**
     * 输出图片到文件中
     *
     * @param        $filename
     * @param string $type
     */
    public function ouputFile($filename, $type = 'jpeg') {
        if ($type == 'jpeg' || $type == 'jpg') {
            imagejpeg($this->imageResource, $filename);
        } else {
            imagesavealpha($this->imageResource, TRUE);
            imagepng($this->imageResource, $filename);
        }
        $this->destory();
    }
    
    /**
     * 销毁所有的头像资源
     */
    private function destory() {
        try{
            foreach ($this->resourceList as $item) {
                if($item){
                    imagedestroy($item);
                }
            }
            imagedestroy($this->imageResource);
        }catch (\Exception $e){
        
        }
    }
    
    
    /**
     * @return int
     */
    public function getWidth() {
        return imagesx($this->imageResource);
    }
    
    /**
     * @return int
     */
    public function getHeight() {
        return imagesy($this->imageResource);
    }
    
    public function getHandler($file) {
        $extension = pathinfo($file);
        if($extension){
            $extension = $extension['extension'];
        }
        if (!in_array($extension, $this->allow_ext)) {
            throw new Exception('类型不允许');
        }
        
        if ($extension == 'jpeg' || $extension == 'jpg') {
            $handler = imagecreatefromjpeg($file);
        } else {
            $handler = imagecreatefrompng($file);
        }
        $this->resourceList[] = $handler;
        
        return $handler;
    }
    
    private function colorallocate($color, $alpha = 0) {
        $color       = strtolower($color);
        $colorLength = strlen($color);
        
        switch ($colorLength) {
            case 6:
                # code...
                $red   = substr($color, 0, 2);
                $green = substr($color, 2, 2);
                $blue  = substr($color, 4, 2);
                break;
            
            case 3:
                # code...
                $red   = substr($color, 0, 1) . "0";
                $green = substr($color, 1, 1) . "0";
                $blue  = substr($color, 2, 1) . "0";
                break;
            
            default:
                throw new Exception("color length error! example:ffffff or fff", 1);
                break;
        }
        
        return imagecolorallocatealpha($this->imageResource, hexdec($red), hexdec($green), hexdec($blue), $alpha);
    }
    
}
