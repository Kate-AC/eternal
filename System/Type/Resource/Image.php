<?php

/**
 * 画像クラス
 */

namespace System\Type\Resource;

use System\Exception\SystemException;

class Image
{
    const TYPE_JPG = 'jpeg';

    const TYPE_PNG = 'png';

    const TYPE_GIF = 'gif';

    const TYPE_BMP = 'bmp';

    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
    private $size;

    /**
     * @var string
     */
    private $uri;

    /**
     * @var resource
     */
    private $resource;

    /**
     * @var resource
     */
    private $compressed;

    /**
     * @var int
     */
    private $originalHeight;

    /**
     * @var int
     */
    private $originalWidth;

    /**
     * @var int
     */
    private $height;

    /**
     * @var int
     */
    private $width;

    /**
     * @var int
     */
    private $heightLimit;

    /**
     * @var int
     */
    private $widthLimit;

    /**
     * @var int
     */
    private $extension;

    /**
     * @var int
     */
    private $originalExtension;

    /**
     * @var int
     */
    private $quality;

    /**
     * コンストラクタ
     *
     * @param string $uri
     * @param string $name
     */
    public function __construct($uri = null, $name = null)
  {
        $this->name = $name;

        if (!is_null($uri)) {
            $data = file_get_contents($uri);
            if (false !== $data) {
                $info                    = getimagesizefromstring($data);
                $this->uri               = $uri;
                $this->size              = filesize($uri);
                $this->extension         = str_replace('image/', '', $info['mime']);
                $this->originalExtension = $this->extension;
                $this->resource          = imagecreatefromstring($data);
                $this->originalHeight    = imagesy($this->resource);
                $this->originalWidth     = imagesx($this->resource);
                $this->height            = imagesy($this->resource);
                $this->width             = imagesx($this->resource);
            }
        }
    }

    /**
     * 名前を取得する
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * サイズを取得する
     *
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * 画像の横幅をセットする
     *
     * @param int $value
     * @return Image
     */
    public function setWidth($value)
    {
        $this->width = $value;
        return $this;
    }

    /**
     * 画像の縦幅をセットする
     *
     * @param int $value
     * @return Image
     */
    public function setHeight($value)
    {
        $this->height = $value;
        return $this;
    }

    /**
     * 画像の横幅の最大値をセットする
     *
     * @param int $value
     * @return Image
     */
    public function setWidthLimit($value)
    {
        $this->widthLimit = $value;
        return $this;
    }

    /**
     * 画像の縦幅の最大値をセットする
     *
     * @param int $value
     * @return Image
     */
    public function setHeightLimit($value)
    {
        $this->heightLimit = $value;
        return $this;
    }

    /**
     * 画像の拡張子タイプをセットする
     *
     * @param int $value
     * @return Image
     */
    public function setExtension($value)
    {
        $this->extension = $value;
        return $this;
    }

    /**
     * 画像のクオリティをセットする
     *
     * @param int $value
     * @return Image
     */
    public function setQuality($value)
    {
        $this->quality = $value;
        return $this;
    }

    /**
     * 画像の横幅を取得する
     *
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * 画像の縦幅を取得する
     *
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * 元の画像の横幅を取得する
     *
     * @return int
     */
    public function getOriginalWidth()
    {
        return $this->originalWidth;
    }

    /**
     * 元の画像の縦幅を取得する
     *
     * @return int
     */
    public function getOriginalHeight()
    {
        return $this->originalHeight;
    }

    /**
     * 縦横の最大値に合わせたサイズにリサイズする
     */
    public function resize()
    {
        if (!is_null($this->heightLimit)) {
            if ($this->heightLimit < $this->height) {
                $ratio = $this->heightLimit / $this->height;

                $this->height = $this->heightLimit;
                $this->width  = $this->width * $ratio;
            }
        }

        if (!is_null($this->widthLimit)) {
            if ($this->widthLimit < $this->width) {
                $ratio = $this->widthLimit / $this->width;

                $this->height = $this->height * $ratio;
                $this->width  = $this->widthLimit;
            }
        }
    }

    /**
     * 画像をコンバートする
     */
    public function convert()
    {
        switch ($this->originalExtension) {
            case self::TYPE_JPG:
                $originalImage = imagecreatefromjpeg($this->uri);
                break;
            case self::TYPE_PNG:
                $originalImage = imagecreatefrompng($this->uri);
                break;
            case self::TYPE_GIF:
                $originalImage = imagecreatefromgif($this->uri);
                break;
            case self::TYPE_BMP:
                $originalImage = imagecreatefrombmp($this->uri);
                break;
        }

        $canvas = imagecreatetruecolor($this->width, $this->height);

        imagecopyresampled(
            $canvas,
            $originalImage,
            0,
            0,
            0,
            0,
            $this->width,
            $this->height,
            $this->originalWidth,
            $this->originalHeight
        );

        $this->compressed = $canvas;
    }

    /**
     * 画像を保存する
     *
     * @param string  $path
     * @param boolean $isShow
     */
    public function save($path = null, $isShow = false)
    {
        if (is_null($path) && false === $isShow) {
            throw new SystemException('画像の保存先が指定されていません');
        }

        $this->resize();
        $this->convert();

        if (true === $isShow) {
            header('Content-Type: image/' . $this->extension);
        }

        switch ($this->extension) {
            case self::TYPE_JPG:
                if (100 < $this->quality || 0 > $this->quality) {
                    throw new SystemException('jpgのqualityに指定できる値は0～100です');
                }
                imagejpeg($this->compressed, $path, $this->quality);
                break;
            case self::TYPE_PNG:
                if (9 < $this->quality || 0 > $this->quality) {
                    throw new SystemException('pngのqualityに指定できる値は0～9です');
                }
                imagepng($this->compressed, $path, $this->quality);
                break;
            case self::TYPE_GIF:
                imagegif($this->compressed, $path);
                break;
            case self::TYPE_BMP:
                imagebmp($this->compressed, $path);
                break;
        }
        imagedestroy($this->compressed);
    }

    /**
     * base64にエンコードした文字列を取得
     */
    public function getBase64()
    {
        ob_start();

        switch ($this->extension) {
            case self::TYPE_JPG:
                if (!is_null($this->quality) && (100 < $this->quality || 0 > $this->quality)) {
                    throw new SystemException('jpgのqualityに指定できる値は0～100です');
                }
                imagejpeg($this->compressed);
                break;
            case self::TYPE_PNG:
                if (!is_null($this->quality) && (9 < $this->quality || 0 > $this->quality)) {
                    throw new SystemException('pngのqualityに指定できる値は0～9です');
                }
                imagepng($this->compressed);
                break;
            case self::TYPE_GIF:
                imagegif($this->compressed);
                break;
            case self::TYPE_BMP:
                imagebmp($this->compressed);
                break;
        }

        $stream  = ob_get_contents();
        $encoded = sprintf('data:image/%s;base64,%s', $this->extension, base64_encode($stream));

        ob_end_clean();
        return $encoded;
    }

    /**
     * 画像を表示する
     */
    public function show()
    {
        $this->save(null, true);
    }
}
