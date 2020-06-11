<?php
// https://gist.github.com/SuryaElite/308f8e9a91ed6f84eeda55288ebfd533
 // Converts text to decimal. Only onne character. Returns in format: 0-255
    function txt2dec($txt)
    {
        return ord($txt);
    }

    // Converts text to hex. Only one character. Returns in format: XX not X
    function txt2hex($txt)
    {
        return bin2hex($txt);
    }

    // Converts text to binary. Only one character. Returns in format: 00000000
    function txt2bin($txt)
    {
        $mybyte = decbin(ord($txt[0]));
        $MyBitSec = substr("00000000",0,8 - strlen($mybyte)) . $mybyte;
        return $MyBitSec;
    }


    // Converts decimal to text. Only one number between 0 and 255. Returns in format: X
    function dec2txt($dec)
    {
        return chr($dec);
    }

    // Converts decimal to hex. Only one number between 0 and 255. Returns in format: XX not X
    function dec2hex($dec)
    {
        $THex = dechex($dec);
        $THex = substr("00",0,2 - strlen($THex)) . $THex;
        return $THex;
    }

    // Converts decimal to binary. Only one number between 0 and 255. Returns in format: 00000000
    function dec2bin($dec)
    {
        $mybyte = decbin($dec);
        $MyBitSec = substr("00000000",0,8 - strlen($mybyte)) . $mybyte;
        return $MyBitSec;
    }


    // Converts hex to text. Only one hex number at a time. Input format can be either X or XX. Returns in format: X
    function hex2txt($hex)
    {
        $hex = substr("00",0,2 - strlen($hex)) . $hex;
        return chr(hexdec($hex));
    }

    // Converts hex to decimal. Only one hex number at a time. Input format can be either X or XX. Returns in format: 0-255
    function hex2dec($hex)
    {
        $hex = substr("00",0,2 - strlen($hex)) . $hex;
        return hexdec($hex);
    }

    // Converts hex to binary. Only one hex number at a time. Input format can be either X or XX. Returns in format: 00000000
    function hex2bin($hex)
    {
        $hex = substr("00",0,2 - strlen($hex)) . $hex;
        $mybyte = decbin(hexdec($hex));
        $MyBitSec = substr("00000000",0,8 - strlen($mybyte)) . $mybyte;
        return $MyBitSec;
    }


    // Converts binary to text. Only one binary number at a time. Input can be X or XX or XXX or XXXX or XXXXX or XXXXXX or XXXXXXX or XXXXXXXX (only 1's and 0's). Returns in format: X
    function bin2txt($bin)
    {
        $bin = substr("00000000",0,8 - strlen($bin)) . $bin;
        return chr(bindec($bin));
    }

    // Converts binary to text. Only one binary number at a time. Input can be X or XX or XXX or XXXX or XXXXX or XXXXXX or XXXXXXX or XXXXXXXX (only 1's and 0's). Returns in format: 0-255.
    function bin2dec($bin)
    {
        $bin = substr("00000000",0,8 - strlen($bin)) . $bin;
        return bindec($bin);
    }

    // Converts binary to hex. Only one binary number at a time. Input can be X or XX or XXX or XXXX or XXXXX or XXXXXX or XXXXXXX or XXXXXXXX (only 1's and 0's). Returns in format: XX
    function bin2hex2($bin)
    {
        $bin = substr("00000000",0,8 - strlen($bin)) . $bin;
        $hex = dechex(bindec($bin));
        $hex = substr("00",0,2 - strlen($hex)) . $hex;
        return $hex;
    }



    // Converts a text sequence to a decimal secuence. Input format: "xxxxxx". Output format: 255 255 255 255 255 255 255
    function txtsec2decsec($txtsec)
    {
        $Data = '';
        for($i=0;$i<strlen($txtsec);$i++)
        {
            $Data .= ord($txtsec[$i]);
            if($i != strlen($txtsec)-1)
                $Data .= " ";
        }
        return $Data;
    }

    // Converts a text sequence to a decimal secuence. Input format: "xxxxxx". Output format: ffffffffffffffff
    function txtsec2hexsec($txtsec)
    {
        $Data = '';
        for($i=0;$i<strlen($txtsec);$i++)
        {
            $Data .= bin2hex($txtsec[$i]);
            //if($i != strlen($txtsec)-1)
            //    $Data .= " ";
        }
        return $Data;
    }

    // Converts a text sequence to a binary secuence. Input format: "xxxxxx". Output format: 00000000000000000000000000000000
    function txtsec2binsec($txtsec)
    {
        $Data = '';
        for($i=0;$i<strlen($txtsec);$i++)
        {
            $mybyte = decbin(ord($txtsec[$i]));
            $MyBitSec = substr("00000000",0,8 - strlen($mybyte)) . $mybyte;
            $Data .= $MyBitSec;
            //if($i != strlen($txtsec)-1)
            //    $Data .= " ";
        }
        return $Data;
    }


    // Converts a decimal sequence to a text sequence. Input format: xxx xxx xxx xxx xxx. Outputformat: "xxxxx"
    function decsec2txtsec($decsec)
    {
        $Data = '';
        $DSplit = explode(" ", $decsec);
        for($i=0;$i<sizeof($DSplit);$i++)
        {
            $Data .= chr($DSplit[$i]);
            //if($i != sizeof($DSplit)-1)
            //    $Data .= " ";
        }
        return $Data;
    }

    // Converts a decimal sequence to a hex sequence. Input format: xxx xxx xxx xxx xxx. Outputformat: ffffffffffff
    function decsec2hexsec($decsec)
    {
        $Data = '';
        $DSplit = explode(" ", $decsec);
        for($i=0;$i<sizeof($DSplit);$i++)
        {
            $THex = dechex($DSplit[$i]);
            $THex = substr("00",0,2 - strlen($THex)) . $THex;
            $Data .= $THex;
            //if($i != sizeof($DSplit)-1)
            //    $Data .= " ";
        }
        return $Data;
    }

    // Converts a decimal sequence to a binary sequence. Input format: xxx xxx xxx xxx xxx. Outputformat: 0000000000000000000000000000000000000000000000000
    function decsec2binsec($decsec)
    {
        $Data = '';
        $DSplit = explode(" ", $decsec);
        for($i=0;$i<sizeof($DSplit);$i++)
        {
            $mybyte = decbin($DSplit[$i]);
            $MyBitSec = substr("00000000",0,8 - strlen($mybyte)) . $mybyte;
            $Data .= $THex;
            //if($i != sizeof($DSplit)-1)
            //    $Data .= " ";
        }
        return $Data;
    }


    // Converts a hex sequence to a text sequence. Input format: ffffffff. Outputformat: "xxxxx"
    function hexsec2txtsec($hexsec)
    {
        $Data = '';
        for($i=0;$i<strlen($hexsec);$i+=2)
        {
            $Data .= chr(hexdec($hexsec[$i].$hexsec[$i+1]));
            //if($i != sizeof($DSplit)-1)
            //    $Data .= " ";
        }
        return $Data;
    }

    // Converts a hex sequence to a decimal sequence. Input format: ffffffff. Outputformat: 255 255 255 255
    function hexsec2decsec($hexsec)
    {
        $Data = '';
        for($i=0;$i<strlen($hexsec);$i+=2)
        {
            $Data .= hexdec($hexsec[$i].$hexsec[$i+1]);
            if($i != strlen($hexsec)-2)
                $Data .= " ";
        }
        return $Data;
    }

    // Converts a hex sequence to a binary sequence. Input format: ffffffff. Outputformat: 0000000000000000000000000000000000000000
    function hexsec2binsec($hexsec)
    {
        $Data = '';
        for($i=0;$i<strlen($hexsec);$i+=2)
        {
            $mybyte = decbin(hexdec($hexsec[$i].$hexsec[$i+1]));
            $MyBitSec = substr("00000000",0,8 - strlen($mybyte)) . $mybyte;
            $Data .= $MyBitSec;
            //if($i != strlen($hexsec)-2)
            //    $Data .= " ";
        }
        return $Data;
    }


    // Converts a binary sequence to a text sequence. Input format: 0000000000000000000000000000000000000000. Outputformat: "xxxxxx"
    function binsec2txtsec($binsec)
    {
        $Data = '';
        for($i=0;$i<strlen($binsec);$i+=8)
        {
            $bin = substr($binsec, $i,;
            $Data .= chr(bindec($bin));
            //if($i != strlen($hexsec)-2)
            //    $Data .= " ";
        }
        return $Data;
    }

    // Converts a binary sequence to a hex sequence. Input format: 0000000000000000000000000000000000000000. Outputformat: ffffffff
    function binsec2hexsec($binsec)
    {
        $Data = '';
        for($i=0;$i<strlen($binsec);$i+=8)
        {
            $bin = substr($binsec, $i,;
            $hex = dechex(bindec($bin));
            $hex = substr("00",0,2 - strlen($hex)) . $hex;
            $Data .= $hex;
            //if($i != strlen($hexsec)-2)
            //    $Data .= " ";
        }
        return $Data;
    }

    // Converts a binary sequence to a decimal sequence. Input format: 0000000000000000000000000000000000000000. Outputformat: 255 255 255 255
    function binsec2decsec($binsec)
    {
        $Data = '';
        for($i=0;$i<strlen($binsec);$i+=8)
        {
            $bin = substr($binsec, $i,;
            $Data .= bindec($bin);
            if($i != strlen($binsec)-8)
                $Data .= " ";
        }
        return $Data;
    }
