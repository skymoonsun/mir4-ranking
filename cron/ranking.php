<?php
require_once("/var/www/app/constant.php");

date_default_timezone_set("Europe/Istanbul");
setlocale(LC_TIME, 'turkish');

$start = strtotime(date('Y-m-d H:i:s'));
$startTime = date('Y-m-d H:i:s');

ini_set("max_execution_time", "-1");
ini_set("memory_limit", "-1");
ignore_user_abort(true);
set_time_limit(0);

$progressDate = strtotime(date('Y-m-d'));
//$progressDate = strtotime('2022-07-02');

$dataScraping = new DataScraping();

$activeClans = array();
$activeUsers = array();

for($i=1; $i<=999; $i++){
    $url = "https://forum.mir4global.com/rank?ranktype=4&worldgroupid=13&worldid=193&classtype=&searchname=&loaded=1&liststyle=ol&page=".$i;

    $content = $dataScraping->getContent($url);

    $clanNames = $dataScraping->getAllData($content, '</em></td><td><span>', '</span></td><td><span>');
    $leaderNames = $dataScraping->getAllData($content, 'span.', 'user_name', 1);
    $powerScore = $dataScraping->getAllData($content, 'td.', 'text_right', 1);
    $ranking = $dataScraping->getAllData($content, 'span.', 'num', 1);

    if($clanNames!=false){
        if(count($clanNames)>0){

            for($j=0; $j<count($clanNames); $j++){

                $clanName = $dataScraping->cleantext($clanNames[$j]);
                $clanRank = $dataScraping->cleanNumber($ranking[$j]);
                $clanLeader = $dataScraping->cleantext($leaderNames[$j]);
                $clanPs = str_replace(",", "", $dataScraping->cleanNumber($powerScore[$j]));

                $existClan = new Clan();
                $existClan = $existClan->search(array("CLAN_NAME" => $clanName));
                if(count($existClan)==0){

                    // if clan exist

                    $addClan = new Clan();

                    $addClan->CLAN_RANK   = $clanRank;
                    $addClan->CLAN_NAME   = $clanName;
                    $addClan->CLAN_LEADER = $clanLeader;
                    $addClan->CLAN_PS     = $clanPs;

                    $addClan->create();

                    $newClan = new Clan();
                    $newClan = $newClan->search(array("CLAN_NAME" => $clanName));

                    $activeClans[] = $newClan[0]['CLAN_ID'];

                }else{

                    // if clan doesn't exist

                    $updateClan = new Db();
                    $updateClan = $updateClan->query("UPDATE table_clan SET 
                      CLAN_RANK = :CLAN_RANK, 
                      CLAN_NAME = :CLAN_NAME, 
                      CLAN_LEADER = :CLAN_LEADER,
                      CLAN_PS = :CLAN_PS
WHERE CLAN_ID = :id", array(
                        "CLAN_RANK"=>$clanRank,
                        "CLAN_NAME"=>$clanName,
                        "CLAN_LEADER"=>$clanLeader,
                        "CLAN_PS"=>$clanPs,
                        "CLAN_SUSPEND"=>0,
                        "id"=>$existClan[0]['CLAN_ID']
                    ));

                    $activeClans[] = $existClan[0]['CLAN_ID'];

                }

            }

        }
    }else{
        break;
    }

}

$clans = new Clan();
$clans = $clans->search();

$allClans = array_column(array_map(
    function (array $elem) {
        unset($elem['CLAN_RANK']);
        unset($elem['CLAN_NAME']);
        unset($elem['CLAN_LEADER']);
        unset($elem['CLAN_PS']);
        unset($elem['CLAN_SUSPEND']);
        return $elem;
    },
    $clans
), 'CLAN_ID');

$suspendClans = array_values(array_diff($allClans, $activeClans));


foreach ($suspendClans as $suspendClan){

    $clanSuspend = new Db();
    $clanSuspend = $clanSuspend->query("UPDATE table_clan SET 
                      CLAN_SUSPEND = :CLAN_SUSPEND
WHERE CLAN_ID = :id", array(
        "CLAN_SUSPEND"=>1,
        "id"=>$suspendClan
    ));
}



for($i=1; $i<=999; $i++){
    $url = "https://forum.mir4global.com/rank?ranktype=1&worldgroupid=13&worldid=193&classtype=&searchname=&loaded=1&liststyle=ol&page=".$i;

    $content = $dataScraping->getContent($url);

    $userNames = $dataScraping->getAllData($content, 'span.', 'user_name', 1);
    $powerScore = $dataScraping->getAllData($content, 'td.', 'text_right', 1);
    $ranking = $dataScraping->getAllData($content, 'span.', 'num', 1);
    $clans = $dataScraping->getAllData($content, '</span></span></td><td><span>', '</span></td><td class="text_right">');

    if($userNames!=false){
        if(count($userNames)>0){

            for($j=0; $j<count($userNames); $j++){

                $userName = $dataScraping->cleantext($userNames[$j]);
                $userRank = $dataScraping->cleanNumber($ranking[$j]);
                $userClan = $dataScraping->cleantext($clans[$j]);
                $userPs = str_replace(",", "", $dataScraping->cleanNumber($powerScore[$j]));

                $existUser = new User();
                $existUser = $existUser->search(array("USER_NAME" => $userName));
                if(count($existUser)==0){

                    // if user exist

                    $searchClan = new Clan();
                    $searchClan = $searchClan->search(array("CLAN_NAME" => $userClan));

                    $addUser = new User();

                    $addUser->USER_RANK   = $userRank;
                    $addUser->USER_NAME   = $userName;

                    if(count($searchClan)>0){
                        $addUser->USER_CLAN = $searchClan[0]['CLAN_ID'];
                    }else{
                        $addUser->USER_CLAN = 0;
                    }

                    $addUser->USER_POWER_SCORE     = $userPs;

                    $addUser->create();

                    $newUser = new User();
                    $newUser = $newUser->search(array("USER_NAME" => $userName));

                    $activeUsers[] = $newUser[0]['USER_ID'];

                }else{

                    // if user doesn't exist

                    //$progressDate = strtotime(date('Y-m-d'));
                    //$progressDate = 1656622800;


                    $existProgress = new Progress();
                    $existProgress = $existProgress->search(array(
                        "USER_ID" => $existUser[0]['USER_ID'],
                        "DATE" => $progressDate
                    ));

                    if(count($existProgress)==0){
                        $psProgress = $dataScraping->abs_diff($userPs, $existUser[0]['USER_POWER_SCORE']);
                        $rankProgress = -1 * $dataScraping->abs_diff($userRank, $existUser[0]['USER_RANK']);


                        $addProgress = new Progress();

                        $addProgress->USER_ID       = $existUser[0]['USER_ID'];
                        $addProgress->OLD_PS        = $existUser[0]['USER_POWER_SCORE'];
                        $addProgress->NEW_PS        = $userPs;
                        $addProgress->PS_PROGRESS   = $psProgress;
                        $addProgress->RANK_PROGRESS = $rankProgress;
                        $addProgress->DATE          = $progressDate;
                        $addProgress->DATE_TEXT     = date('Y-m-d', $progressDate);

                        $addProgress->create();
                    }


                    $searchClan = new Clan();
                    $searchClan = $searchClan->search(array("CLAN_NAME" => $userClan));

                    if(count($searchClan)>0){
                        $userNewClan = $searchClan[0]['CLAN_ID'];
                    }else{
                        $userNewClan = 0;
                    }

                    $updateUser = new Db();
                    $updateUser = $updateUser->query("UPDATE table_user SET 
                      USER_CLAN = :USER_CLAN, 
                      USER_RANK = :USER_RANK, 
                      USER_POWER_SCORE = :USER_POWER_SCORE 
WHERE USER_ID = :id", array(
                        "USER_CLAN"=>$userNewClan,
                        "USER_RANK"=>$userRank,
                        "USER_POWER_SCORE"=>$userPs,
                        "USER_SUSPEND"=>0,
                        "id"=>$existUser[0]['USER_ID']
                    ));

                    $activeUsers[] = $existUser[0]['USER_ID'];

                }

            }

        }
    }else{
        break;
    }

}

$users = new User();
$users = $users->search();

$allUsers = array_column(array_map(
    function (array $elem) {
        unset($elem['USER_CLAN']);
        unset($elem['USER_RANK']);
        unset($elem['USER_NAME']);
        unset($elem['USER_POWER_SCORE']);
        unset($elem['USER_SUSPEND']);
        return $elem;
    },
    $users
), 'USER_ID');

$suspendUsers = array_values(array_diff($allUsers, $activeUsers));


foreach ($suspendUsers as $suspendUser){

    $userSuspend = new Db();
    $userSuspend = $userSuspend->query("UPDATE table_user SET 
                      USER_SUSPEND = :USER_SUSPEND
WHERE USER_ID = :id", array(
        "USER_SUSPEND"=>1,
        "id"=>$suspendUser
    ));
}

exportDatabase("database","root","password","mir4_db",  false, "mir4_db.sql" );

importDatabase('185.162.146.170','mir4_admin','Hqc6k*20','mir4_db');

$end = strtotime(date('Y-m-d H:i:s'));

$txt = fopen('/var/www/app/result.txt', 'w');
fwrite($txt, $startTime."\n".gmdate("H:i:s", abs($start-$end)));
fclose($txt);

//echo gmdate("H:i:s", abs($start-$end));
?>