<?php

use App\Enums\EForecastStatus;

if (!function_exists('jalaliMonthNumberToNames'))
{
    function loadMenu($location, $limit = 500, $class = null)
    {
        $menuLocationId = App\Models\MenuLocation\MenuLocation::query()->where('title', 'header-menu')->value('id');
        $globalMenu = app(App\Services\SiteMenuBuilder\SiteMenuBuilder::class)->generate($menuLocationId, $limit, $class);

        return $globalMenu;
    }

    function jalaliMonthNumberToNames($month, $shorten = false, $len = 3)
    {
        switch ($month) {
            case '1':
                $ret = App\Enums\EJalaliMonths::search(1);
                break;
            case '2':
                $ret = App\Enums\EJalaliMonths::search(2);
                break;
            case '3':
                $ret = App\Enums\EJalaliMonths::search(3);
                break;
            case '4':
                $ret = App\Enums\EJalaliMonths::search(4);
                break;
            case '5':
                $ret = App\Enums\EJalaliMonths::search(5);
                break;
            case '6':
                $ret = App\Enums\EJalaliMonths::search(6);
                break;
            case '7':
                $ret = App\Enums\EJalaliMonths::search(7);
                break;
            case '8':
                $ret = App\Enums\EJalaliMonths::search(8);
                break;
            case '9':
                $ret = App\Enums\EJalaliMonths::search(9);
                break;
            case '10':
                $ret = App\Enums\EJalaliMonths::search(10);
                break;
            case '11':
                $ret = App\Enums\EJalaliMonths::search(11);
                break;
            case '12':
                $ret = App\Enums\EJalaliMonths::search(12);
                break;
            default:
                $ret = '';
                break;
        }

        return ($shorten) ? mb_substr($ret, 0, $len, 'UTF-8') : $ret;
    }
}

if (!function_exists('gregorianMonthNumberToNames'))
{
    function gregorianMonthNumberToNames($month, $shorten = false, $len = 3)
    {
        switch ($month) {
            case '1':
                $ret = App\Enums\EGregorianMonths::search(1);
                break;
            case '2':
                $ret = App\Enums\EGregorianMonths::search(2);
                break;
            case '3':
                $ret = App\Enums\EGregorianMonths::search(3);
                break;
            case '4':
                $ret = App\Enums\EGregorianMonths::search(4);
                break;
            case '5':
                $ret = App\Enums\EGregorianMonths::search(5);
                break;
            case '6':
                $ret = App\Enums\EGregorianMonths::search(6);
                break;
            case '7':
                $ret = App\Enums\EGregorianMonths::search(7);
                break;
            case '8':
                $ret = App\Enums\EGregorianMonths::search(8);
                break;
            case '9':
                $ret = App\Enums\EGregorianMonths::search(9);
                break;
            case '10':
                $ret = App\Enums\EGregorianMonths::search(10);
                break;
            case '11':
                $ret = App\Enums\EGregorianMonths::search(11);
                break;
            case '12':
                $ret = App\Enums\EGregorianMonths::search(12);
                break;
            default:
                $ret = '';
                break;
        }

        return ($shorten) ? mb_substr($ret, 0, $len, 'UTF-8') : $ret;
    }
}

if (!function_exists('removeExtraWhitespacess'))
{
    function removeExtraWhitespaces($string)
    {
        return preg_replace('/[\t\n\r\s]+/', ' ', $string);
    }
}

if (!function_exists('removeAllWhitespaces'))
{
    function removeAllWhitespaces($string)
    {
        return preg_replace('/[\t\n\r\s]+/', '', $string);
    }
}

if (!function_exists('slugify')) {
    function slugify($string, $separator = '-', $limit = 150)
    {
        $string = mb_strtolower($string);
        $string = mb_ereg_replace('/\s+/', ' ', $string);
        $string = \Illuminate\Support\Str::words($string, $limit, '');
        $string = mb_ereg_replace('([^آ-ی۰-۹a-z0-9_]|-)+', $separator, $string);

        return trim($string, $separator);
    }
}

if (!function_exists('getNumbersFromString')) {
    function getNumbersFromString($string) {
        preg_match_all('!\d+(?:\.\d+)?!', $string, $matches);

        return $matches[0];
    }
}

if (!function_exists('numeralSystemConverter'))
{
    function numeralSystemConverter($number, $FromNumeralSystem, $ToNumeralSystem) {
        $numerals = [
            'persian' => ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'],
            'arabic' => ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'],
            'english' => ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
        ];
        $number = str_replace($numerals[$FromNumeralSystem], $numerals[$ToNumeralSystem], $number);

        return $number;
    }
}

if (!function_exists('arabicLettersToPersian'))
{
    function arabicLettersToPersian($letters) {
        return str_replace(['ك', 'ي'], ['ک', 'ی'], $letters);
    }
}

if (!function_exists('persianLettersToArabic'))
{
    function persianLettersToArabic($letters) {
        return str_replace(['ک', 'ی'], ['ك', 'ي'], $letters);
    }
}

if (!function_exists('limitWords'))
{
    function limitWords($words, $limit = 100, $end = '...') {
        return Str::words($words, $limit, $end);
    }
}

if (!function_exists('calcTotalOdds'))
{
    function calcTotalOdds($details, $roundNumber = 3) {
        $totalOddValue = 0;
        if (is_array($details)) {
            if (count($details) > 0) {
                $totalOdd = 1;

                foreach ($details as $detail) {
                    $odd = dataOdDecrypt($detail['odd'], $detail['eventId']);
                    $totalOddValue = $totalOdd*$odd;
                    $totalOdd = $totalOddValue;
                }
            }
        }

        return $totalOddValue !== 0 ? round($totalOddValue, $roundNumber) : $totalOddValue;
    }
}

if (!function_exists('dataOdEncrypt'))
{
    function dataOdEncrypt($od, $dataEventId) {
        $startRandomStr = Str::random(5);
        $endRandomStr   = Str::random(4);
        $salt = substr(md5($dataEventId), 0, 5);
        $pepper = substr(md5($dataEventId), 7, 5);
        $str = base64_encode($od);
        $str = trim($str, '==');
        $str = $salt . $str . $pepper ;
        $str = $startRandomStr . $str . $endRandomStr;
        $str = strrev($str);
        $str = base64_encode($str);
        $str = trim($str, '==');

        return $str;
    }
}

if (!function_exists('dataOdDecrypt'))
{
    function dataOdDecrypt($encryptedOd, $dataEventId) {
        $salt = substr(md5($dataEventId), 0, 5);
        $pepper = substr(md5($dataEventId), 7, 5);
        $str = $encryptedOd . '==';
        $str = base64_decode($str);
        $str = strrev($str);
        $str = substr_replace($str, '', 0, 5);
        $str = substr_replace($str, '', -4);
        $str = str_replace([$salt, $pepper], '', $str);
        $str = $str . '==';
        $str = base64_decode($str);

        return $str;
    }
}

if (!function_exists('sanitizeString')) {
    function sanitizeString($string)
    {
        $string = preg_replace('/[\t\n\r\s]+/', '', $string);
        $string = strip_tags($string);
        $string = mb_strtolower($string);
        $string = str_replace(['.', ',', '/', '-', '$', '*', ':', ';', '!', '?', '|', '', '_', '<', '>', '#', '~', '"', "'", '^', '(', ')', '=', '+'], '', $string);

        return $string;
    }
}

if (!function_exists('singleCurl'))
{
    function singleCurl($url, $output = 'json') {
        $events = '';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($ch, CURLOPT_TCP_FASTOPEN, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        $data = curl_exec($ch);
        if ($data === false) {
            $info = curl_getinfo($ch);
            curl_close($ch);
            die('error occured during curl exec. Additioanl info: ' . var_export($info));
        }
        curl_close($ch);

        if ($output === 'json') {
            $events = json_decode($data);
        }

        if ($output === 'array') {
            $events = json_decode($data, true);
        }

        return $events;
    }
}

if (!function_exists('multiCurl'))
{
    function multiCurl($arrayUrl, $output = 'json') {
        $content = [];
        $ch = [];
        $mh = curl_multi_init();
        $i = 0;

        foreach($arrayUrl as $url) {
            $ch[$i] = curl_init();
            curl_setopt($ch[$i], CURLOPT_URL, $url);
            curl_setopt($ch[$i], CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            curl_setopt($ch[$i], CURLOPT_TCP_FASTOPEN, 1);
            curl_setopt($ch[$i], CURLOPT_HEADER, 0);
            curl_setopt($ch[$i], CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch[$i], CURLOPT_ENCODING, 'gzip,deflate');
            curl_setopt($ch[$i], CURLOPT_TIMEOUT, 120);
            curl_setopt($ch[$i], CURLOPT_CONNECTTIMEOUT, 10);
            curl_multi_add_handle($mh, $ch[$i]);
            $i ++;
        }
        $active = null;
        do {
            $mrc = curl_multi_exec($mh, $active);
            usleep(10); // Maybe needed to limit CPU load (See P.S.)
        } while ($active);

        foreach ($ch AS $i => $c) {
            if ($output === 'json') {
                $content[$i] = json_decode(curl_multi_getcontent($c));
            }

            if ($output === 'array') {
                $content[$i] = json_decode(curl_multi_getcontent($c), true);
            }

            curl_multi_remove_handle($mh, $c);
        }
        curl_multi_close($mh);

        return $content;
    }
}

if (!function_exists('parseEvents'))
{
    function parseEvents($result, $eventString) {
        if (isset($result['events'])) {
            $eventText = [];
            foreach ($result['events'] as $event) {
                if (stripos($event['text'], $eventString)) {
                    $event['text'] = removeExtraWhitespaces($event['text']);
                    $eventTextArray = explode(' - ', $event['text']);
                    $eventTextArrayCount = count($eventTextArray);
                    if ($eventTextArrayCount >= 3) {
                        $time   = str_replace("'", '', removeExtraWhitespaces($eventTextArray[0]));
                        $number = str_replace("'", '', removeExtraWhitespaces($eventTextArray[1]));
                        $team   = str_replace("'", '', removeExtraWhitespaces(implode(' - ', array_slice($eventTextArray, 2))));
                        $eventText[$time] = [$number, $team];
                    }
                    else {
                        return false;
                    }
                }
            }

            return $eventText;
        }

        return null;
    }
}

if (!function_exists('calcFulltimeResult'))
{
    function calcFulltimeResult($forecast, $result) {
        $home = 0;
        $away = 0;

        if (intval($result['time_status']) != 333333333) {
            if (in_array(intval($result['sport_id']), [1, 18])) {
                foreach ($result['scores'] as $level => $score) {
                    $home = intval($score['home']);
                    $away = intval($score['away']);
                }
            }

            if (in_array(intval($result['sport_id']), [13, 91, 92])) {
                foreach ($result['scores'] as $level => $score) {
                    $home += intval($score['home']) > intval($score['away']) ? 1 : 0;
                    $away += intval($score['away']) > intval($score['home']) ? 1 : 0;
                }
            }

            if ($home > $away) {
                $originalName = [$result['home']['name'], '1'];
            }
            elseif ($home < $away) {
                $originalName = [$result['away']['name'], '2'];
            }
            elseif ($home == $away) {
                $originalName = ['X'];
            }

            if (in_array($forecast['originalName'], $originalName)) {
                $forecast['eventResult'] = EForecastStatus::WIN;
            }
            else {
                $forecast['eventResult'] = EForecastStatus::LOSE;
            }

            return [
                'forecast' => $forecast,
                'result'   => $forecast['eventResult'],
            ];
        }

        return null;
    }
}

if (!function_exists('calcMatchWinner')) {
    function calcMatchWinner($forecast, $result) {
        $home = 0;
        $away = 0;

        if (intval($result['time_status']) != 333333333) {
            if (in_array(intval($result['sport_id']), [1, 18])) {
                foreach ($result['scores'] as $level => $score) {
                    $home = intval($score['home']);
                    $away = intval($score['away']);
                }
            }

            if (in_array(intval($result['sport_id']), [13, 91, 92])) {
                foreach ($result['scores'] as $level => $score) {
                    $home += intval($score['home']) > intval($score['away']) ? 1 : 0;
                    $away += intval($score['away']) > intval($score['home']) ? 1 : 0;
                }
            }

            $homeName = sanitizeString($result['home']['name']);
            $awayName = sanitizeString($result['away']['name']);
            $originalName = sanitizeString($forecast['originalName']);

            similar_text($homeName, $originalName,  $homePercent);
            similar_text($awayName, $originalName,  $awayPercent);

            if ($homePercent == $awayPercent) {
                $homeStrpos = is_int(mb_strpos($originalName, $homeName));
                $awayStrpos = is_int(mb_strpos($originalName, $awayName));
                if ($homeStrpos === true) {
                    $originalName = [$result['home']['name'], '1'];
                }
                elseif ($awayStrpos === true) {
                    $originalName = [$result['away']['name'], '2'];
                }
                else {
                    $originalName = null;
                }
            }
            elseif ($homePercent > $awayPercent) {
                $originalName = [$result['home']['name'], '1'];
            }
            elseif ($homePercent < $awayPercent) {
                $originalName = [$result['away']['name'], '2'];
            }
            else {
                $originalName = null;
            }

            if (!is_array($originalName) && is_null($originalName)) {
                $forecast['eventResult'] = EForecastStatus::AUTOMATE_CALCULATION_FAILED;
            }
            elseif (in_array($forecast['originalName'], $originalName)) {
                $forecast['eventResult'] = EForecastStatus::WIN;
            }
            else {
                $forecast['eventResult'] = EForecastStatus::LOSE;
            }

            return [
                'forecast' => $forecast,
                'result'   => $forecast['eventResult'],
            ];
        }

        return null;
    }
}

if (!function_exists('calcMoneyLine'))
{
    function calcMoneyLine($forecast, $result) {
        $home = 0;
        $away = 0;

        if (intval($result['time_status']) != 333333333) {
            if (in_array(intval($result['sport_id']), [1, 18])) {
                foreach ($result['scores'] as $level => $score) {
                    $home = intval($score['home']);
                    $away = intval($score['away']);
                }
            }

            if (in_array(intval($result['sport_id']), [13, 91, 92])) {
                foreach ($result['scores'] as $level => $score) {
                    $home += intval($score['home']) > intval($score['away']) ? 1 : 0;
                    $away += intval($score['away']) > intval($score['home']) ? 1 : 0;
                }
            }

            $homeName = sanitizeString($result['home']['name']);
            $awayName = sanitizeString($result['away']['name']);
            $originalName = sanitizeString($forecast['originalName']);

            similar_text($homeName, $originalName,  $homePercent);
            similar_text($awayName, $originalName,  $awayPercent);

            if ($homePercent == $awayPercent) {
                $homeStrpos = is_int(mb_strpos($originalName, $homeName));
                $awayStrpos = is_int(mb_strpos($originalName, $awayName));
                if ($homeStrpos === true) {
                    $originalName = [$result['home']['name'], '1'];
                }
                elseif ($awayStrpos === true) {
                    $originalName = [$result['away']['name'], '2'];
                }
                else {
                    $originalName = null;
                }
            }
            elseif ($homePercent > $awayPercent) {
                $originalName = [$result['home']['name'], '1'];
            }
            elseif ($homePercent < $awayPercent) {
                $originalName = [$result['away']['name'], '2'];
            }
            else {
                $originalName = null;
            }

            if (!is_array($originalName) && is_null($originalName)) {
                $forecast['eventResult'] = EForecastStatus::AUTOMATE_CALCULATION_FAILED;
            }
            elseif (in_array($forecast['originalName'], $originalName)) {
                $forecast['eventResult'] = EForecastStatus::WIN;
            }
            else {
                $forecast['eventResult'] = EForecastStatus::LOSE;
            }

            return [
                'forecast' => $forecast,
                'result'   => $forecast['eventResult'],
            ];
        }

        return null;
    }
}

if (!function_exists('calcDoubleChance')) {
    function calcDoubleChance($forecast, $result)
    {
        $home = 0;
        $away = 0;

        if (intval($result['time_status']) != 333333333) {
            if (in_array(intval($result['sport_id']), [1, 18])) {
                foreach ($result['scores'] as $level => $score) {
                    $home = intval($score['home']);
                    $away = intval($score['away']);
                }
            }

            if (in_array(intval($result['sport_id']), [13, 91, 92])) {
                foreach ($result['scores'] as $level => $score) {
                    $home += intval($score['home']) > intval($score['away']) ? 1 : 0;
                    $away += intval($score['away']) > intval($score['home']) ? 1 : 0;
                }
            }

            if ($home > $away OR $home == $away) {
                $originalName = ['1X'];
            }
            elseif ($home < $away OR $home == $away) {
                $originalName = ['X2'];
            }
            elseif ($home > $away OR $home < $away) {
                $originalName = ['12'];
            }

            if (in_array($forecast['originalName'], $originalName)) {
                $forecast['eventResult'] = EForecastStatus::WIN;
            }
            else {
                $forecast['eventResult'] = EForecastStatus::LOSE;
            }

            return [
                'forecast' => $forecast,
                'result'   => $forecast['eventResult'],
            ];
        }

        return null;
    }
}

if (!function_exists('calc1stHalfDoubleChance')) {
    function calc1stHalfDoubleChance($forecast, $result)
    {
        $home = 0;
        $away = 0;

        if (intval($result['time_status']) != 333333333) {
            if (in_array(intval($result['sport_id']), [1, 18])) {
                foreach ($result['scores'] as $level => $score) {
                    if ($level == 1) {
                        $home = intval($score['home']);
                        $away = intval($score['away']);
                    }
                }
            }

            if (in_array(intval($result['sport_id']), [13, 91, 92])) {
                foreach ($result['scores'] as $level => $score) {
                    if ($level == 1) {
                        $home += intval($score['home']) > intval($score['away']) ? 1 : 0;
                        $away += intval($score['away']) > intval($score['home']) ? 1 : 0;
                    }
                }
            }

            if ($home > $away OR $home == $away) {
                $originalName = ['1X'];
            }
            elseif ($home < $away OR $home == $away) {
                $originalName = ['X2'];
            }
            elseif ($home > $away OR $home < $away) {
                $originalName = ['12'];
            }

            if (in_array($forecast['originalName'], $originalName)) {
                $forecast['eventResult'] = EForecastStatus::WIN;
            }
            else {
                $forecast['eventResult'] = EForecastStatus::LOSE;
            }

            return [
                'forecast' => $forecast,
                'result'   => $forecast['eventResult'],
            ];
        }

        return null;
    }
}

if (!function_exists('calcHalfTimeFullTime')) {
    function calcHalfTimeFullTime($forecast, $result)
    {
        $halfTimeHome = 0;
        $fullTimeHome = 0;
        $halfTimeAway = 0;
        $fullTimeAway = 0;

        if (intval($result['time_status']) != 333333333) {
            if (in_array(intval($result['sport_id']), [1, 18])) {
                foreach ($result['scores'] as $level => $score) {
                    if ($level == 1) {
                        $halfTimeHome = intval($score['home']);
                        $halfTimeAway = intval($score['away']);
                    }
                    $fullTimeHome = intval($score['home']);
                    $fullTimeAway = intval($score['away']);
                }
            }

            if (in_array(intval($result['sport_id']), [13, 91, 92])) {
                foreach ($result['scores'] as $level => $score) {
                    if ($level == 1) {
                        $halfTimeHome += intval($score['home']) > intval($score['away']) ? 1 : 0;
                        $halfTimeAway += intval($score['away']) > intval($score['home']) ? 1 : 0;
                    }
                    $fullTimeHome += intval($score['home']) > intval($score['away']) ? 1 : 0;
                    $fullTimeAway += intval($score['away']) > intval($score['home']) ? 1 : 0;
                }
            }

            list($halfTime, $fullTime) = explode('-', $forecast['originalName']);
            $halfTime = trim($halfTime);
            $fullTime = trim($fullTime);
            $awayName = sanitizeString($result['away']['name']);
            $homeName = sanitizeString($result['home']['name']);
            $halfTimeOriginalName = sanitizeString($halfTime);
            $fullTimeOriginalName = sanitizeString($fullTime);

            similar_text($homeName, $halfTimeOriginalName,  $halfTimeHomePercent);
            similar_text($homeName, $fullTimeOriginalName,  $fullTimeHomePercent);
            similar_text($awayName, $halfTimeOriginalName,  $halfTimeAwayPercent);
            similar_text($awayName, $fullTimeOriginalName,  $fullTimeAwayPercent);

            if ($halfTimeOriginalName == 'draw') {
                $halfTimeOriginalName = ['draw'];
            }
            else {
                if ($halfTimeHomePercent == $halfTimeAwayPercent) {
                    $halfTimeHomeStrpos = is_int(mb_strpos($halfTimeOriginalName, $homeName));
                    $halfTimeAwayStrpos = is_int(mb_strpos($halfTimeOriginalName, $awayName));
                    if ($halfTimeHomeStrpos === true) {
                        $halfTimeOriginalName = [$result['home']['name']];
                    }
                    elseif ($halfTimeAwayStrpos === true) {
                        $halfTimeOriginalName = [$result['away']['name']];
                    }
                    else {
                        $halfTimeOriginalName = null;
                    }
                }
                elseif ($halfTimeHomePercent > $halfTimeAwayPercent) {
                    $halfTimeOriginalName = [$result['home']['name']];
                }
                elseif ($halfTimeHomePercent < $halfTimeAwayPercent) {
                    $halfTimeOriginalName = [$result['away']['name']];
                }
                else {
                    $halfTimeOriginalName = null;
                }
            }

            if ($fullTimeOriginalName == 'draw') {
                $fullTimeOriginalName = ['draw'];
            }
            else {
                if ($fullTimeHomePercent == $fullTimeAwayPercent) {
                    $fullTimeHomeStrpos = is_int(mb_strpos($fullTimeOriginalName, $homeName));
                    $fullTimeAwayStrpos = is_int(mb_strpos($fullTimeOriginalName, $awayName));
                    if ($fullTimeHomeStrpos === true) {
                        $fullTimeOriginalName = [$result['home']['name']];
                    }
                    elseif ($fullTimeAwayStrpos === true) {
                        $fullTimeOriginalName = [$result['away']['name']];
                    }
                    else {
                        $fullTimeOriginalName = null;
                    }
                }
                elseif ($fullTimeHomePercent > $fullTimeAwayPercent) {
                    $fullTimeOriginalName = [$result['home']['name']];
                }
                elseif ($fullTimeHomePercent < $fullTimeAwayPercent) {
                    $fullTimeOriginalName = [$result['away']['name']];
                }
                else {
                    $fullTimeOriginalName = null;
                }
            }

            $tempForcast = [];

            if (!is_array($halfTimeOriginalName) && is_null($halfTimeOriginalName)) {
                $tempForcast['halfTimeEventResult'] = EForecastStatus::AUTOMATE_CALCULATION_FAILED;
            }
            elseif (in_array($halfTime, $halfTimeOriginalName)) {
                $tempForcast['halfTimeEventResult'] = EForecastStatus::WIN;
            }
            else {
                $tempForcast['halfTimeEventResult'] = EForecastStatus::LOSE;
            }

            if (!is_array($fullTimeOriginalName) && is_null($fullTimeOriginalName)) {
                $tempForcast['fullTimeEventResult'] = EForecastStatus::AUTOMATE_CALCULATION_FAILED;
            }
            elseif (in_array($fullTime, $fullTimeOriginalName)) {
                $tempForcast['fullTimeEventResult'] = EForecastStatus::WIN;
            }
            else {
                $tempForcast['fullTimeEventResult'] = EForecastStatus::LOSE;
            }

            if (in_array(EForecastStatus::AUTOMATE_CALCULATION_FAILED, $tempForcast)) {
                $forecast['eventResult'] = EForecastStatus::AUTOMATE_CALCULATION_FAILED;
            }
            elseif ($tempForcast['halfTimeEventResult'] == EForecastStatus::WIN && $tempForcast['fullTimeEventResult'] == EForecastStatus::WIN) {
                $forecast['eventResult'] = EForecastStatus::WIN;
            }
            else {
                $forecast['eventResult'] = EForecastStatus::LOSE;
            }

            return [
                'forecast' => $forecast,
                'result'   => $forecast['eventResult'],
            ];
        }

        return null;
    }
}

if (!function_exists('calcFinalScore')) {
    function calcFinalScore($forecast, $result)
    {
        $home = 0;
        $away = 0;

        if (intval($result['time_status']) != 333333333) {
            if (in_array(intval($result['sport_id']), [1])) {
                foreach ($result['scores'] as $level => $score) {
                    $home = intval($score['home']);
                    $away = intval($score['away']);
                }
            }

            $originalName = ["{$home}-{$away}"];

            if (in_array($forecast['originalName'], $originalName)) {
                $forecast['eventResult'] = EForecastStatus::WIN;
            }
            else {
                $forecast['eventResult'] = EForecastStatus::LOSE;
            }

            return [
                'forecast' => $forecast,
                'result'   => $forecast['eventResult'],
            ];
        }

        return null;
    }
}

if (!function_exists('calcGoalsOddEven')) {
    function calcGoalsOddEven($forecast, $result)
    {
        $home = 0;
        $away = 0;

        if (intval($result['time_status']) != 333333333) {
            if (in_array(intval($result['sport_id']), [1, 18])) {
                foreach ($result['scores'] as $level => $score) {
                    $home = intval($score['home']);
                    $away = intval($score['away']);
                }
            }

            if (in_array(intval($result['sport_id']), [13, 91, 92])) {
                foreach ($result['scores'] as $level => $score) {
                    $home += intval($score['home']) > intval($score['away']) ? 1 : 0;
                    $away += intval($score['away']) > intval($score['home']) ? 1 : 0;
                }
            }

            $totalScores = $home + $away;
            if ($totalScores % 2 == 0) {
                $winner = ['Even'];
            }
            else {
                $winner = ['Odd'];
            }

            if (in_array($forecast['originalName'], $winner)) {
                $forecast['eventResult'] = EForecastStatus::WIN;
            }
            else {
                $forecast['eventResult'] = EForecastStatus::LOSE;
            }

            return [
                'forecast' => $forecast,
                'result'   => $forecast['eventResult'],
            ];
        }

        return null;
    }
}

if (!function_exists('calcDrawNoBet')) {
    function calcDrawNoBet($forecast, $result)
    {
        $home = 0;
        $away = 0;

        if (intval($result['time_status']) != 333333333) {
            if (in_array(intval($result['sport_id']), [1])) {
                foreach ($result['scores'] as $level => $score) {
                    $home = intval($score['home']);
                    $away = intval($score['away']);
                }
            }


            if ($home > $away) {
                $originalName = [$result['home']['name'], '1'];
            }
            elseif ($home < $away) {
                $originalName = [$result['away']['name'], '2'];
            }

            if (in_array($forecast['originalName'], $originalName)) {
                $forecast['eventResult'] = EForecastStatus::WIN;
            }
            elseif ($home  == $away) {
                $forecast['eventResult'] = EForecastStatus::DRAW;
            }
            else {
                $forecast['eventResult'] = EForecastStatus::LOSE;
            }

            return [
                'forecast' => $forecast,
                'result'   => $forecast['eventResult'],
            ];
        }

        return null;
    }
}

if (!function_exists('calc1stGoal')) {
    function calc1stGoal($forecast, $result)
    {

    }
}

if (!function_exists('calcMatchGoals')) {
    function calcMatchGoals($forecast, $result)
    {
        $home = 0;
        $away = 0;

        if (intval($result['time_status']) != 333333333) {
            if (in_array(intval($result['sport_id']), [1, 18])) {
                foreach ($result['scores'] as $level => $score) {
                    $home = intval($score['home']);
                    $away = intval($score['away']);
                }
            }

            if (in_array(intval($result['sport_id']), [13, 91, 92])) {
                foreach ($result['scores'] as $level => $score) {
                    $home += intval($score['home']) > intval($score['away']) ? 1 : 0;
                    $away += intval($score['away']) > intval($score['home']) ? 1 : 0;
                }
            }

            $forecast['originalName'] = removeExtraWhitespaces($forecast['originalName']);
            $matchWinnerValue = getNumbersFromString($forecast['originalName'])[0];
            $matchWinnerTitle = trim(str_replace($matchWinnerValue, '', $forecast['originalName']));

            $totalScores = $home + $away;
            if ($matchWinnerValue > $totalScores) {
                $originalName = ['Over'];
            }
            elseif($matchWinnerValue < $totalScores) {
                $originalName = ['Under'];
            }

            if (!in_array($matchWinnerTitle, ['Over', 'Under'])) {
                $forecast['eventResult'] = EForecastStatus::AUTOMATE_CALCULATION_FAILED;
            }
            elseif ($totalScores == $matchWinnerValue) {
                $forecast['eventResult'] = EForecastStatus::DRAW;
            }
            elseif (in_array($matchWinnerTitle, $originalName)) {
                $forecast['eventResult'] = EForecastStatus::WIN;
            }
            else {
                $forecast['eventResult'] = EForecastStatus::LOSE;
            }

            return [
                'forecast' => $forecast,
                'result'   => $forecast['eventResult'],
            ];
        }

        return null;
    }
}

if (!function_exists('calcFirstHalfGoals')) {
    function calcFirstHalfGoals($forecast, $result)
    {
        $home = 0;
        $away = 0;

        if (intval($result['time_status']) != 333333333) {
            if (in_array(intval($result['sport_id']), [1, 18])) {
                foreach ($result['scores'] as $level => $score) {
                    if($level == 1)
                    {
                        $home = intval($score['home']);
                        $away = intval($score['away']);
                    }
                }
            }

            if (in_array(intval($result['sport_id']), [13, 91, 92])) {
                foreach ($result['scores'] as $level => $score) {
                    if($level == 1)
                    {
                        $home += intval($score['home']) > intval($score['away']) ? 1 : 0;
                        $away += intval($score['away']) > intval($score['home']) ? 1 : 0;
                    }
                }
            }

            $forecast['originalName'] = removeExtraWhitespaces($forecast['originalName']);
            $matchWinnerValue = getNumbersFromString($forecast['originalName'])[0];
            $matchWinnerTitle = trim(str_replace($matchWinnerValue, '', $forecast['originalName']));

            $totalScores = $home + $away;
            if (floatval($matchWinnerValue) > $totalScores) {
                $originalName = ['Over'];
            }
            elseif(floatval($matchWinnerValue) < $totalScores) {
                $originalName = ['Under'];
            }

            if (!in_array($matchWinnerTitle, ['Over', 'Under'])) {
                $forecast['eventResult'] = EForecastStatus::AUTOMATE_CALCULATION_FAILED;
            }
            elseif ($totalScores == $matchWinnerValue) {
                $forecast['eventResult'] = EForecastStatus::DRAW;
            }
            elseif (in_array($matchWinnerTitle, $originalName)) {
                $forecast['eventResult'] = EForecastStatus::WIN;
            }
            else {
                $forecast['eventResult'] = EForecastStatus::LOSE;
            }

            return [
                'forecast' => $forecast,
                'result'   => $forecast['eventResult'],
            ];
        }

        return null;
    }
}

if (!function_exists('calcAsianHandicap')) {
    function calcAsianHandicap($forecast, $result)
    {

    }
}

if (!function_exists('calcAlternativeAsianHandicap')) {
    function calcAlternativeAsianHandicap($forecast, $result)
    {

    }
}

if (!function_exists('calcGoalLine')) {
    function calcGoalLine($forecast, $result)
    {

    }
}

if (!function_exists('calcMatchResultAndTotal')) {
    function calcMatchResultAndTotal($forecast, $result)
    {

    }
}

if (!function_exists('calcTeamTotal')) {
    function calcTeamTotal($forecast, $result)
    {

    }
}

if (!function_exists('calcTotalCorners')) {
    function calcTotalCorners($forecast, $result)
    {
        if (intval($result['time_status']) != 333333333) {
            $homeCorners = $result['stats']['corners'][0];
            $awayCorners = $result['stats']['corners'][1];
            $totalCorners = $homeCorners + $awayCorners;

            $forecast['originalName'] = removeExtraWhitespaces($forecast['originalName']);
            $matchWinnerValue = getNumbersFromString($forecast['originalName'])[0];
            $matchWinnerTitle = trim(str_replace($matchWinnerValue, '', $forecast['originalName']));

            if ($totalCorners > $matchWinnerValue) {
                $originalName = ['Over'];
            }
            elseif($totalCorners < $matchWinnerValue) {
                $originalName = ['Under'];
            }
            elseif($matchWinnerValue == $totalCorners) {
                $originalName = ['Exactly'];
            }

            if (!in_array($matchWinnerTitle, ['Over', 'Under', 'Exactly'])) {
                $forecast['eventResult'] = EForecastStatus::AUTOMATE_CALCULATION_FAILED;
            }
            elseif (in_array($matchWinnerTitle, $originalName)) {
                $forecast['eventResult'] = EForecastStatus::WIN;
            }
            else {
                $forecast['eventResult'] = EForecastStatus::LOSE;
            }

            return [
                'forecast' => $forecast,
                'result'   => $forecast['eventResult'],
            ];
        }

        return null;
    }
}

if (!function_exists('calc2WayCorners')) {
    function calc2WayCorners($forecast, $result)
    {
        if (intval($result['time_status']) != 333333333) {
            $homeCorners = $result['stats']['corners'][0];
            $awayCorners = $result['stats']['corners'][1];
            $totalCorners = $homeCorners + $awayCorners;

            $forecast['originalName'] = removeExtraWhitespaces($forecast['originalName']);
            $matchWinnerValue = getNumbersFromString($forecast['originalName'])[0];
            $matchWinnerTitle = trim(str_replace($matchWinnerValue, '', $forecast['originalName']));

            if ($totalCorners > $matchWinnerValue) {
                $originalName = ['Over'];
            }
            elseif($totalCorners < $matchWinnerValue) {
                $originalName = ['Under'];
            }

            if (!in_array($matchWinnerTitle, ['Over', 'Under'])) {
                $forecast['eventResult'] = EForecastStatus::AUTOMATE_CALCULATION_FAILED;
            }
            elseif ($totalCorners == $matchWinnerValue) {
                $forecast['eventResult'] = EForecastStatus::DRAW;
            }
            elseif (in_array($matchWinnerTitle, $originalName)) {
                $forecast['eventResult'] = EForecastStatus::WIN;
            }
            else {
                $forecast['eventResult'] = EForecastStatus::LOSE;
            }

            return [
                'forecast' => $forecast,
                'result'   => $forecast['eventResult'],
            ];
        }

        return null;
    }
}

if (!function_exists('calc1stHalfCorners')) {
    function calc1stHalfCorners($forecast, $result)
    {
        if (intval($result['time_status']) != 333333333) {
            $eventCornerText = parseEvents($result, 'Corner');

            if ($eventCornerText !== false && is_array($eventCornerText)) {
                $eventTimesArray = array_keys($eventCornerText);
                $firstHalfWithExtraTime = preg_grep("/^(45+\+\d+)/", $eventTimesArray);
                $firstHalfCorners = 0;
                if (count($firstHalfWithExtraTime) > 0) {
                    $firstHalfCorners = intval(key($firstHalfWithExtraTime)) + 1;
                }
                else {
                    $newEventTimesArray = array_filter($eventTimesArray, function ($value) {
                        return ($value <= 45);
                    });

                    if ($newEventTimesArrayCount = count($newEventTimesArray) > 0) {
                        $firstHalfCorners = $newEventTimesArrayCount;
                    }
                }

                $forecast['originalName'] = removeExtraWhitespaces($forecast['originalName']);
                $matchWinnerValue = getNumbersFromString($forecast['originalName'])[0];
                $matchWinnerTitle = trim(str_replace($matchWinnerValue, '', $forecast['originalName']));

                if ($firstHalfCorners > $matchWinnerValue) {
                    $originalName = ['Over'];
                }
                elseif($firstHalfCorners < $matchWinnerValue) {
                    $originalName = ['Under'];
                }

                if (!in_array($matchWinnerTitle, ['Over', 'Under'])) {
                    $forecast['eventResult'] = EForecastStatus::AUTOMATE_CALCULATION_FAILED;
                }
                elseif ($firstHalfCorners == $matchWinnerValue) {
                    $forecast['eventResult'] = EForecastStatus::DRAW;
                }
                elseif (in_array($matchWinnerTitle, $originalName)) {
                    $forecast['eventResult'] = EForecastStatus::WIN;
                }
                else {
                    $forecast['eventResult'] = EForecastStatus::LOSE;
                }
            }
            else {
                $forecast['eventResult'] = EForecastStatus::AUTOMATE_CALCULATION_FAILED;
            }

            return [
                'forecast' => $forecast,
                'result'   => $forecast['eventResult'],
            ];
        }

        return null;
    }
}
