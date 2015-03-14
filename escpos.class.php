<?php

/**
 * ESC/POS
 *
 * ESC/POS print driver for PHP
 * This driver has been know to work with Epson TM-T20
 *
 * @version	20150315
 * @author	Guillaume Marsay <guillaume@futurolan.net>
 * @link	https://github.com/Futurolan/escpos
 */

class ESCPOS
{
  const NUL = "\x00";
  const LF = "\x0a";
  const ESC = "\x1b";
  const GS = "\x1d";
  const MODE_FONT_A = 0;
  const MODE_FONT_B = 1;
  const MODE_EMPHASIZED = 8;
  const MODE_DOUBLE_HEIGHT = 16;
  const MODE_DOUBLE_WIDTH = 32;
  const MODE_UNDERLINE = 128;
  const FONT_A = 0;
  const FONT_B = 1;
  const FONT_C = 2;
  const JUSTIFY_LEFT = 0;
  const JUSTIFY_CENTER = 1;
  const JUSTIFY_RIGHT = 2;
  const CUT_FULL = 65;
  const CUT_PARTIAL = 66;
  const BARCODE_UPCA = 0;
  const BARCODE_UPCE = 1;
  const BARCODE_JAN13 = 2;
  const BARCODE_JAN8 = 3;
  const BARCODE_CODE39 = 4;
  const BARCODE_ITF = 5;
  const BARCODE_CODABAR = 6;

  private $fp;


  function __construct($fp = null)
  {
    if (is_null($fp))
    {
      $fp = fopen("php://stdout", "wb");
    }

    $this->fp = $fp;
    $this->initialize();
  }


  function initialize()
  {
    fwrite($this->fp, self::ESC . "@");
  }


  function text($str = "")
  {
    fwrite($this->fp, $str);
  }


  function feed($lines = 1)
  {
    if ($lines <= 1)
    {
      fwrite($this->fp, self::LF);
    }
    else
    {
      fwrite($this->fp, self::ESC . "d" . chr($lines));
    }
  }


  /* 
  * MODE_FONT_A
  * MODE_FONT_B
  * MODE_EMPHASIZED
  * MODE_DOUBLE_HEIGHT
  * MODE_DOUBLE_WIDTH
  * MODE_UNDERLINE
  */
  function select_print_mode($mode)
  {
    fwrite($this->fp, self::ESC . "!" . chr($mode));
  }

  
  function text_normal($str)
  {
    fwrite($this->fp, self::ESC . "!" . chr(self::MODE_FONT_A));
    $this->text($str);
  }

  
  function text_small($str)
  {
    fwrite($this->fp, self::ESC . "!" . chr(self::MODE_FONT_B));
    $this->text($str);
  }

  
  function text_bold($str)
  {
    fwrite($this->fp, self::ESC . "!" . chr(self::MODE_EMPHASIZED));
    $this->text($str);
  }

  
  function text_underline($str)
  {
    fwrite($this->fp, self::ESC . "!" . chr(self::MODE_UNDERLINE));
    $this->text($str);
  }

  
  function text_double_height($str)
  {
    fwrite($this->fp, self::ESC . "!" . chr(self::MODE_DOUBLE_HEIGHT));
    $this->text($str);
  }

  
  function text_double_width($str)
  {
    fwrite($this->fp, self::ESC . "!" . chr(self::MODE_DOUBLE_WIDTH));
    $this->text($str);
  }

  
  // $underline 0 for no underline, 1 for underline, 2 for heavy underline
  function set_underline($underline = 1)
  {
    fwrite($this->fp, self::ESC . "-". chr($underline));
  }


  // $on true for emphasis, false for no emphasis
  function set_emphasis($on = true)
  {
    fwrite($this->fp, self::ESC . "E". ($on ? chr(1) : chr(0)));
  }


  // @param boolean $on true for double strike, false for no double strike
  function set_double_strike($on)
  {
    fwrite($this->fp, self::ESC . "G". ($on ? chr(1) : chr(0)));
  }


  // Font must be FONT_A, FONT_B, or FONT_C.
  function set_font($font)
  {
    fwrite($this->fp, self::ESC . "M" . chr($font));
  }


  // Justification must be JUSTIFY_LEFT, JUSTIFY_CENTER, or JUSTIFY_RIGHT.
  function set_justification($justification)
  {
    fwrite($this->fp, self::ESC . "a" . chr($justification));
  }
  
  
  function justify_left()
  {
    fwrite($this->fp, self::ESC . "a" . chr(self::JUSTIFY_LEFT));
  }
  
  
  function justify_center()
  {
    fwrite($this->fp, self::ESC . "a" . chr(self::JUSTIFY_CENTER));
  }
  
  
  function justify_right()
  {
    fwrite($this->fp, self::ESC . "a" . chr(self::JUSTIFY_RIGHT));
  }


  // $lines number of lines to feed
  function feed_reverse($lines = 1)
  {
    fwrite($this->fp, self::ESC . "e" . chr($lines));
  }


  // $mode Cut mode, either CUT_FULL or CUT_PARTIAL
  // $lines Number of lines to feed
  function cut($mode = self::CUT_FULL, $lines = 3)
  {
    fwrite($this->fp, self::GS . "V" . chr($mode) . chr($lines));
  }


  // height of barcode (dots)
  function set_barcode_height($height)
  {
    fwrite($this->fp, self::GS . "h" . chr($height));
  }


  // string $content
  // int $type
  function barcode($content, $type = self::BARCODE_CODE39)
  {
    fwrite($this->fp, self::GS . "k" . chr($type) . $content . self::NUL);
  }


  /*
  * $content : string or URL
  * $size : 1 <= n <= 16
  * $err : 48 <= n <= 51
  */
  function qrcode($content, $size = 5, $err = 50)
  {
    // data - function 80
    fwrite($this->fp, self::GS . "(" . "k" . chr(strlen($content)+3) . chr(0) . chr(49) . chr(80) . chr(48) . $content);
    // error correction - function 69
    fwrite($this->fp, self::GS . "(" . "k" . chr(3) . chr(0) . chr(49) . chr(69) . chr($err));
    // size - function 67
    fwrite($this->fp, self::GS . "(" . "k" . chr(3) . chr(0) . chr(49) . chr(67) . chr($size));
    // print - function 81
    fwrite($this->fp, self::GS . "(" . "k" . chr(3) . chr(0) . chr(49) . chr(81) . chr(48));
  }
}
