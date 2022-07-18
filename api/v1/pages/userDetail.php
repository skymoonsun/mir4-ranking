<?php

if(array_key_exists('userName', $_GET)){
    if(array_key_exists('day', $_GET)){
        if(temizleInt(temizleGet($_GET['day']))>0){

            $searchUser = new User();
            $searchUser = $searchUser->search(array("USER_NAME" => temizleGet2($_GET['userName']), "USER_SUSPEND" => 0));

            if(count($searchUser)>0){


                $progress = new Progress();
                $progress = $progress->search(array("USER_ID" => $searchUser[0]['USER_ID']), array("DATE" => "DESC"), array(0 => temizleInt(temizleGet($_GET['day']))));

                $clan = new Clan();
                $clan = $clan->search(array("CLAN_ID" => $searchUser[0]['USER_CLAN'], "CLAN_SUSPEND" => 0));

                $powerScoreDiff = $progress[0]['NEW_PS']-$progress[count($progress)-1]['NEW_PS'];

                echo json_encode(
                    array(
                        "userName" => $searchUser[0]['USER_NAME'],
                        "userRank" => $searchUser[0]['USER_RANK'],
                        "userClan" => $clan[0]['CLAN_NAME'],
                        "progressDay" => count($progress),
                        "oldPowerScore" => number_format($progress[count($progress)-1]['NEW_PS'],0,"","."),
                        "nowPowerScore" => number_format($progress[0]['NEW_PS'],0,"","."),
                        "powerScoreDiff" => $powerScoreDiff
                    ),
                    JSON_UNESCAPED_UNICODE
                );

            }else{
                echo json_encode(array(
                    'code' => '400',
                    'message' => 'User not found!'
                ), JSON_UNESCAPED_UNICODE);
            }
        }else{
            echo json_encode(array(
                'code' => '400',
                'message' => 'Day parameter is missing!'
            ), JSON_UNESCAPED_UNICODE);
        }
    }else{
        echo json_encode(array(
            'code' => '400',
            'message' => 'Day parameter is missing!'
        ), JSON_UNESCAPED_UNICODE);
    }
}else{
    echo json_encode(array(
        'code' => '400',
        'message' => 'User name is missing!'
    ), JSON_UNESCAPED_UNICODE);
}