<?php

/**
 * Imageのテスト
 */

namespace Test\System\Type\Resource;

use System\Type\Resource\Image;
use System\Exception\SystemException;
use Phantom\Phantom;
use Test\TestHelper;

class TestImage extends TestHelper
{
    /**
     * __construct
     * getName
     * getSize
     */
    public function __constructAndGetNameAndGetSizeTest()
    {
        $path = TEST_DIR . 'dummy.png';
        $image = new Image(__FILE__, 'hoge');
        $this->compareInstance('System\Type\Resource\Image', $image, 'インスタンス生成');
        $this->compareValue('hoge', $image->getName(), '名前の取得');
        $this->compareValue(true, is_int($image->getSize()), 'サイズの取得');
    }

    /**
     * getWidth
     * getHeight
     * getOriginalWidth
     * getOriginalHeight
     */
    public function getWidthAndGetHeightAndGetOriginalWidthAndGetOriginalHeightTest()
    {
        $image = new Image(__DIR__, 'hoge');

        $this->compareValue(100, $image->getWidth(), 'width');
        $this->compareValue(100, $image->getHeight(), 'height');
        $this->compareValue(100, $image->getOriginalWidth(), 'originalWidth');
        $this->compareValue(100, $image->getOriginalHeight(), 'originalHeight');
    }

    /**
     * setWidth
     * setHeight
     * setWidthLimit
     * setHeightLimit
     */
    public function setWidthSetHeightSetWidthLimitSetHeightLimitTest()
    {
        $image = Phantom::m('System\Type\Resource\Image');

        $image->setWidth(100);
        $image->setHeight(101);
        $image->setWidthLimit(102);
        $image->setHeightLimit(103);

        $this->compareValue(100, $image->width, 'width');
        $this->compareValue(101, $image->height, 'height');
        $this->compareValue(102, $image->widthLimit, 'widthLimit');
        $this->compareValue(103, $image->heightLimit, 'heightLimit');
    }

    /**
     * setExtension
     * setQuality
     */
    public function setExtensionSetQualityTest()
    {
        $image = Phantom::m('System\Type\Resource\Image');

        $image->setExtension(100);
        $image->setQuality(101);

        $this->compareValue(100, $image->extension, 'width');
        $this->compareValue(101, $image->quality, 'height');
    }

    /**
     * resize
     */
    public function resizeTest()
    {
        $image = Phantom::m('System\Type\Resource\Image');

        $image->setHeightLimit(400);
        $image->setWidthLimit(600);
        $image->setHeight(800);
        $image->setWidth(600);
        $image->resize();

        $this->compareValueLax(400, $image->height, 'height');
        $this->compareValueLax(300, $image->width, 'width');
    }

    /**
     * convert
     */
    public function convertTest()
    {
        $image = Phantom::m('System\Type\Resource\Image');
        $image->uri            = 'uri';
        $image->width          = 100;
        $image->height         = 100;
        $image->originalWidth  = 200;
        $image->originalHeight = 200;

        $image->originalExtension = Image::TYPE_JPG;
        $image->convert();
        $this->compareValue('canvas', $image->compressed, 'jpg');

        $image->originalExtension = Image::TYPE_PNG;
        $image->convert();
        $this->compareValue('canvas', $image->compressed, 'png');

        $image->originalExtension = Image::TYPE_GIF;
        $image->convert();
        $this->compareValue('canvas', $image->compressed, 'gif');

        $image->originalExtension = Image::TYPE_BMP;
        $image->convert();
        $this->compareValue('canvas', $image->compressed, 'bmp');
    }

    /**
     * save
     * show
     */
    public function saveAndShowTest()
    {
        $image = Phantom::m('System\Type\Resource\Image')
            ->setMethod('resize')
            ->setArgs()
            ->setReturn(null)
            ->exec();

        $image
            ->setMethod('convert')
            ->setArgs()
            ->setReturn(null)
            ->exec();

        try {
            $image->save(null, false);
            $this->throwError('例外が発生すべき箇所で発生していない');
        } catch (SystemException $e) {
            $this->compareException('画像の保存先が指定されていません', $e, 'save 画像の保存先が未指定');
        }

        $image->compressed = 'compressed';

        $image->extension  = Image::TYPE_JPG;
        $image->setQuality(50);
        $this->compareValue(null, $image->save('path'), 'save jpg');
        try {
            $image->setQuality(-1);
            $image->save('path');
            $this->throwError('例外が発生すべき箇所で発生していない');
        } catch (SystemException $e) {
            $this->compareException('jpgのquality', $e, 'save 画像のクオリティが範囲外 jpg -1');
        }

        try {
            $image->setQuality(101);
            $image->save('path');
            $this->throwError('例外が発生すべき箇所で発生していない');
        } catch (SystemException $e) {
            $this->compareException('jpgのquality', $e, 'save 画像のクオリティが範囲外 jpg 101');
        }

        $image->extension  = Image::TYPE_PNG;
        $image->setQuality(5);
        $this->compareValue(null, $image->save('path'), 'save png');
        try {
            $image->setQuality(-1);
            $image->save('path');
            $this->throwError('例外が発生すべき箇所で発生していない');
        } catch (SystemException $e) {
            $this->compareException('pngのquality', $e, 'save 画像のクオリティが範囲外 png -1');
        }

        try {
            $image->setQuality(10);
            $image->save('path');
            $this->throwError('例外が発生すべき箇所で発生していない');
        } catch (SystemException $e) {
            $this->compareException('pngのquality', $e, 'save 画像のクオリティが範囲外 png 10');
        }

        $image->extension = Image::TYPE_GIF;
        $this->compareValue(null, $image->save('path'), 'save gif');

        $this->compareValue(null, $image->show(), 'show');

        // $image->extension = Image::TYPE_BMP;
        // $this->compareValue(null, $image->save('path'), 'save bmp');

    }
}

namespace System\Type\Resource;

/**
 * imagecreatefromjpegのオーバーライド
 */
function imagecreatefromjpeg($uri)
{
    return 'image';
}

/**
 * imagecreatefrompngのオーバーライド
 */
function imagecreatefrompng($uri)
{
    return 'image';
}

/**
 * imagecreatefromgifのオーバーライド
 */
function imagecreatefromgif($uri)
{
    return 'image';
}

/**
 * imagecreatefrombmpのオーバーライド
 */
function imagecreatefrombmp($uri)
{
    return 'image';
}

/**
 * imagecreatetruecolorのオーバーライド
 */
function imagecreatetruecolor($width, $height)
{
    if (100 === $width && 100 === $height) {
        return 'canvas';
    }
    throw new \Exception('Test: imagecreatetruecolor時のエラー');
}

/**
 * imagecopyresampledのオーバーライド
 */
function imagecopyresampled($canvas, $image, $a, $b, $c, $d, $width, $height, $originalWidth, $originalHeight)
{
    if ('canvas' === $canvas && 'image' === $image && 0 === $a && 0 === $b && 0 === $c && 0 === $d && 100 === $width && 100 === $height && 200 === $originalWidth && 200 === $originalHeight) {
        return null;
    }
    throw new \Exception('Test: imagecreateresampled時のエラー');
}

/**
 * imagedestroyのオーバーライド
 */
function imagedestroy($compressed)
{
    if ('compressed' === $compressed) {
        return 'compressed';
    }
    throw new \Exception('Test: imagedestroy時のエラー');
}

/**
 * headerのオーバーライド
 */
function header($value)
{
    return null;
}

/**
 * imagejpegのオーバーライド
 */
function imagejpeg($compressed, $path, $quality)
{
    if ('compressed' === $compressed && 'path' === $path) {
        return null;
    }
    throw new \Exception('Test: imagejpeg時のエラー');
}

/**
 * imagepngのオーバーライド
 */
function imagepng($compressed, $path, $quality)
{
    if ('compressed' === $compressed && 'path' === $path) {
        return null;
    }
    throw new \Exception('Test: imagepng時のエラー');
}

/**
 * imagegifのオーバーライド
 */
function imagegif($compressed, $path)
{
    if ('compressed' === $compressed && 'path' === $path) {
        return null;
    }
    throw new \Exception('Test: imagegif時のエラー');
}

/**
 * imagebmpのオーバーライド
 */
function imagebmp($compressed, $path)
{
    if ('compressed' === $compressed && 'path' === $path) {
        return null;
    }

    if ('compressed' === $compressed && is_null($path)) {
        return null;
    }

    throw new \Exception('Test: imagebmp時のエラー');
}

/**
 * file_get_contentsのオーバーライド
 */
function file_get_contents($value)
{
    return 'data';
}

/**
 * filesizeのオーバーライド
 */
function filesize($value)
{
    return 100;
}

/**
 * getimagesizefromstringのオーバーライド
 */
function getimagesizefromstring($value)
{
    if (null !== $value) {
        return ['mime' => 'image/jpeg'];
    }
    throw new \Exception('Test: getimagesizefromstringのエラー');
}

/**
 * imagecreatefromstringのオーバーライド
 */
function imagecreatefromstring($value)
{
    if (null !== $value) {
        return 'resource';
    }
    throw new \Exception('Test: imagecreatefromstringのエラー');
}

/**
 * imagesyのオーバーライド
 */
function imagesy($value)
{
    if ('resource' === $value) {
        return 100;
    }
    throw new \Exception('Test: imagesyのエラー');
}

/**
 * imagesxのオーバーライド
 */
function imagesx($value)
{
    if ('resource' === $value) {
        return 100;
    }
    throw new \Exception('Test: imagesxのエラー');
}
