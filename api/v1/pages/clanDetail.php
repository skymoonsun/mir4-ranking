<?php

if(array_key_exists('clanName', $_GET)){

    $searchClan = new Clan();
    $searchClan = $searchClan->search(array("CLAN_NAME" => temizle($_GET['clanName']), "CLAN_SUSPEND" => 0));

    if(count($searchClan)>0){
        $searchUser = new User();
        $searchUser = $searchUser->search(array("USER_CLAN" => $searchClan[0]['CLAN_ID'], "USER_SUSPEND" => 0));

        $searchUser = array_map(function($tag) {
            return array(
                'userRank' => $tag['USER_RANK'],
                'userName' => $tag['USER_NAME'],
                'userPs' => number_format($tag['USER_POWER_SCORE'],0,"",".")
            ); }, $searchUser);

        usort($searchUser, function($a, $b) {
            return $b['userPs'] - $a['userPs'];
        });

        $result['userCount'] = count($searchUser);
        $result['userLeader'] = $searchClan[0]['CLAN_LEADER'];
        $result['clanPs'] = number_format($searchClan[0]['CLAN_PS'],0,"",".");
        $result['clanRank'] = $searchClan[0]['CLAN_RANK'];
        $result['userList'] = $searchUser;


        echo json_encode(
            $result,
            JSON_UNESCAPED_UNICODE
        );
    }else{
        echo json_encode(array(
            'code' => '400',
            'message' => 'Clan name is invalid!'
        ), JSON_UNESCAPED_UNICODE);
    }
}else{
    echo json_encode(array(
        'code' => '400',
        'message' => 'Clan name is missing!'
    ), JSON_UNESCAPED_UNICODE);
}