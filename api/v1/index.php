<?php
require_once("/var/www/vhosts/mir4.skymoonsun.dev/constant.php");

header('Access-Control-Allow-Origin: *');

if(!empty($_GET)){
    if(array_key_exists('type', $_GET) && !empty(temizleGet($_GET["type"]))){
        switch (temizleGet($_GET["type"])) {

            case "clanDetail":
                require_once("pages/clanDetail.php");
                break;

            case "userDetail":
                require_once("pages/userDetail.php");
                break;

            default:


        }
    }
}