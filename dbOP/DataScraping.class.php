<?php
require_once("/var/www/app/dbOP/Zebra_cURL.php");
require_once("/var/www/app/dbOP/simple_html_dom.php");

class DataScraping
{
    public function preg($bas, $son, $yazi){
        @preg_match_all('/' . preg_quote($bas, '/') .
            '(.*?)'. preg_quote($son, '/').'/i', $yazi, $m);
        return @$m[1];
    }

    public function getSingleData($link, $tag1, $tag2, $dom=0, $sira=0){

        $arrContextOptions=array(
            "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ),
            'http' => array(
                'method' => 'GET',
                'timeout' => 30,
                'ignore_errors' => true,
            ));

        $curl = new Zebra_cURL();

        $icerik = $curl->scrap($link, true);

        if($dom==1){
            $html = str_get_html($icerik);

            $tagg = $tag1.$tag2;

            if($html === false){

            }else{

                $fiyat = $html->find($tagg, $sira);

                if(is_object($fiyat)) {
                    $fiyat = $fiyat->plaintext;
                }else{
                    if($fiyat==0 || empty($fiyat) || $fiyat==""){


                        if (false !== ($icerik = @file_get_contents($link, false, stream_context_create($arrContextOptions)))) {
                            $icerik = @str_get_html(file_get_contents($link, false, stream_context_create($arrContextOptions)));
                            $fiyat = $icerik->find($tagg, $sira);
                            if(is_object($fiyat)) {
                                $fiyat = $fiyat->plaintext;
                            }
                        } else {
                            $fiyat = 0;
                        }


                    }
                }

                if(!empty($fiyat)){
                    return $fiyat;
                }else{
                    return 0;
                }
            }
        }elseif($dom==0){
            $fiyat = $this->preg($tag1, $tag2, $icerik);

            if(array_key_exists($sira, $fiyat)){
                if($fiyat[$sira]==0){
                    $icerik = file_get_contents($link, false, stream_context_create($arrContextOptions));
                    $fiyat = $this->preg($tag1, $tag2, $icerik);
                }
            }


            if(!empty($fiyat[$sira])){
                return $fiyat[$sira];
            }else{
                return 0;
            }
        }




    }

    public function abs_diff($v1, $v2) {
        $diff = $v1 - $v2;
        $result = $diff < 0 ? (-1) * $diff : $diff;

        if($v1<$v2){
            return -1 * $result;
        }else{
            return $result;
        }
    }

    public function getContent($link){
        $arrContextOptions=array(
            "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ),
            'http' => array(
                'method' => 'GET',
                'timeout' => 30,
                'ignore_errors' => true,
            ));

        $curl = new Zebra_cURL();

        $icerik = $curl->scrap($link, true);

        $icerik = preg_replace("/\s+|\n+|\r/", ' ', $icerik);
        $icerik = preg_replace('/\>\s+\</m', '><', $icerik);

        if(empty(trim($icerik))){
            $icerik = @file_get_contents($link, false, stream_context_create($arrContextOptions));
            $icerik = preg_replace("/\s+|\n+|\r/", ' ', $icerik);
            $icerik = preg_replace('/\>\s+\</m', '><', $icerik);
        }

        if(!empty(trim($icerik))){
            return $icerik;
        }else{
            return false;
        }
    }

    public function getAllData($icerik, $tag1, $tag2, $dom=0)
    {

        if ($icerik) {

            if ($dom == 1) {
                $html = str_get_html($icerik);

                $tagg = $tag1 . $tag2;

                if ($html === false) {

                } else {

                    $fiyat = $html->find($tagg);


                    if (count($fiyat) > 0) {
                        array_map(function ($val) {
                            return $val->plaintext;
                        }, $fiyat);
                    } else {
                        if ($fiyat == 0 || empty($fiyat) || $fiyat == "") {

                            if (false !== $icerik) {

                                $fiyat = $icerik->find($tagg);
                                if (count($fiyat) > 0) {
                                    array_map(function ($val) {
                                        return $val->plaintext;
                                    }, $fiyat);
                                }
                            } else {
                                $fiyat = false;
                            }


                        }
                    }

                    if (count($fiyat) > 0) {
                        return $fiyat;
                    } else {
                        return false;
                    }
                }
            } elseif ($dom == 0) {
                $fiyat = $this->preg($tag1, $tag2, $icerik);


                if (count($fiyat) == 0) {
                    $icerik = file_get_contents($link, false, stream_context_create($arrContextOptions));
                    $icerik = preg_replace("/\s+|\n+|\r/", ' ', $icerik);
                    $icerik = preg_replace('/\>\s+\</m', '><', $icerik);
                    $fiyat = $this->preg($tag1, $tag2, $icerik);
                }

                if (count($fiyat) > 0) {
                    return $fiyat;
                } else {
                    return false;
                }
            }


        }else{
            return false;
        }


    }

    public function cleanGet($veri){
        return trim(strip_tags(htmlentities(str_replace("'", "\'", $veri))));
    }

    public function cleanNumber($veri){
        return preg_replace("#[^0-9]#",'', $this->cleanGet($veri));
    }

    public function cleanText($veri){
        return trim(strip_tags($veri));
    }

}