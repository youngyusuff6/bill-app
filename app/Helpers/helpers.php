<?php

use Illuminate\Support\Facades\Cache;
use App\Services\SSLWalletEncryptionService;

use Carbon\Carbon;

/**
 * Tokenize a user ID into a 16-character string.
 *
 * @param  int|string  $id
 * @return string
 */
function tokenize($id): string
{
    // Convert ID to string
    $id = (string) $id;

    // Use SSL Wallet Encryption Service
    $service = new SSLWalletEncryptionService();

    // Encrypt the ID
    $encrypted = $service->encrypt($id);

    // Create a unique, consistent 16-character token
    $token = substr(md5($encrypted . config('app.key')), 0, 16);

    // Store the encrypted ID in cache with the token
    Cache::put('token:' . $token, $encrypted, now()->addHour());

    return $token;
}

/**
 * Detokenize a token to get the original user ID.
 *
 * @param  string  $token
 * @return int
 * @throws Exception
 */
function detokenize(string $token): int
{
    try {
        // Validate token length
        if (strlen($token) !== 16) {
            throw new \Exception('Invalid token length');
        }

        // Retrieve encrypted ID from cache
        $encrypted = Cache::get('token:' . $token);

        if (!$encrypted) {
            throw new \Exception('Token not found or expired');
        }

        // Use SSL Wallet Encryption Service
        $service = new SSLWalletEncryptionService();

        // Decrypt the ID
        $decryptedId = $service->decrypt($encrypted);

        return (int) $decryptedId;
    } catch (\Exception $e) {
        throw new \Exception('Invalid or expired token: ' . $e->getMessage());
    }
}



/* A FUNCTION TO HELP US REPLACE A TEXT WITHIN A STRING. if the counter parameter is passed,, this will be set to the number of replacements performed.  */
function Text_in_String_REPLACER($Text_to_Lookout, $Replacer, $Full_String_To_Lookin, &$counter = null)
{
    return str_replace($Text_to_Lookout, $Replacer, $Full_String_To_Lookin, $counter);
}









/* A FUNCTION TO HELP US SEARCH OUT FOR A TEXT WITHIN A STRING. THIS FUNCTION RETURNS 'FALSE' IF THE TEXT CANNOT BE FOUND INSIDE THE MAIN STRING AND
    * IT RETURNS AN POSITIVE INTEGER WHEN IT FINDS THE TEXT INSIDE THE STRING. THE INTEGER IT RETURNS SIGNIFIES THE STARTING INDEX POSITION OF THE TEXT
    * INSIDE THE MAIN STRING. TAKE FOR EXAMPLE [Text_in_String_SEARCHER("Hello world!", "world") ===> outputs is 6; THATS BECAUSE THE FIRST CHARACTER
    * POSITION IN THE MAIN STRING IS '0' THAT IS TO SAY THE COUNTING STARTS FROM ZERO. ].  */
function Text_in_String_SEARCHER($Text_to_Lookout, $Full_String_To_Lookin)
{
    return strpos($Full_String_To_Lookin, $Text_to_Lookout);
}









/*  A function to truncate any string. This function factors the length of the "$dots" string parameter, into the overall length of "$string"
        before a string is cutted short  */
function truncator($string, $length, $dots = "...")
{
    /*  Return Result  */
    return (strlen($string) > $length) ? substr($string, 0, $length - strlen($dots)) . $dots : $string;
}












/*  A function to truncate any string. This function doesn't factor the length of the "$dots" string parameter, into the overall length of "$string"
        before a string is cutted short  */
function NON_RESPONSIVE_truncator($string, $length, $dots = "...")
{
    /*  Return Result  */
    return (strlen($string) > $length) ? substr($string, 0, $length) . $dots : $string;
}













// FUNCTION TO HELP US FETCH THE IP ADDRESS OF THE WEBSITE VISITORS
function getUserIpAddr()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        //ip from share internet
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        //ip pass from proxy
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}












// FUNCTION TO  PROCESS A STRING AND LOOK FOR A PATTERN AT THE START OF A STRING AND REMOVE THAT PATTERN FROM THAT STRING. THE FIRST PARAMETER 
// TAKES THE FULL STRING WHILE THE SECOND PARAMETER TAKES IN THE PATTERN TO BE ELIMINATED.
function LEFT_CHOPPER($SAMPLE_STRING, $PATTERN_TO_REMOVE)
{
    // In order to be able to eliminate this pattern if found at the start of our sample string and with great precision we need revers the 
    // sample string so that the begining can become the end, also after reversing it, we will also revers the pattern too so that string 
    // matching can work, this way we can now use the chop function to delete the pattern if found at the start of our string. Finally we have 
    // to reverse the result to set back our string to it right orientation and we return final result.
    return strrev(chop(strrev($SAMPLE_STRING), strrev($PATTERN_TO_REMOVE)));
}












// FUNCTION TO  PROCESS A STRING AND LOOK FOR A PATTERN AT THE END/EDGE OF A STRING AND REMOVE THAT PATTERN FROM THAT STRING. THE FIRST PARAMETER 
// TAKES THE FULL STRING WHILE THE SECOND PARAMETER TAKES IN THE PATTERN TO BE ELIMINATED.
function RIGHT_CHOPPER($SAMPLE_STRING, $PATTERN_TO_REMOVE)
{
    // we use php chop function to first get rid of this pattern should it be found at the end of our sample string. 
    return chop($SAMPLE_STRING, $PATTERN_TO_REMOVE);
}














// FUNCTION TO PROCESS A STRING AND LOOK FOR A PATTERN IN THE BEGINNING AND END OF THAT STRING. IF THIS PATTERN IS FOUND INBETWEEN THE STRING
// IT WOULD BE IGNORED, THIS FUNCTION ONLY ATTACKS THE STRING IF PATTERN STARTS OR ENDS THE STRING. THE FIRST PARAMETER TAKES THE FULL STRING
// WHILE THE SECOND PARAMETER TAKES IN THE PATTERN TO BE ELIMINATED.
function DOUBLE_EDGED_CHOPPER($SAMPLE_STRING, $PATTERN_TO_REMOVE)
{
    // we use php chop function to first get rid of this pattern should it be found at the end of our sample string. 
    $FIRST_EDGE_PROCESSED = RIGHT_CHOPPER($SAMPLE_STRING, $PATTERN_TO_REMOVE);
    // after getting rid of the pattern found at the end of the string, in order to be able to do thesame for the begining of the string and with
    // great precision we need revers the processed-sample-string ($FIRST_EDGE_PROCESSED) so that the begining can become the end, also after
    // reversing it, we will also revers the pattern too so that string matching can work, this way we can re-use the chop function again to delete
    // the pattern if found at the start of our string. Finally we have to reverse the result to set back our string to it right orientation and 
    // we return final result.
    return LEFT_CHOPPER($FIRST_EDGE_PROCESSED, $PATTERN_TO_REMOVE);
}









// FUNCTION TO HELP US PROCESS A DATA AND AFFIRM IF ITS A "LINK-TO-USER-SOCIAL-HANDLE" OR A USERNAME, IF ITS MERELY A USERNAME THIS FUNCTION 
// RETURNS THE USERNAME WITHOUT FURTHER PROCESSING, BUT IF IT IS A SOCIAL HANDLE DIRECT LINK, THIS FUNCTION PROCESSES THIS LINK TO EXTRACT THE
// USER NAME ALONE OUT OF IT.
function SOCIAL_HANDLE_NAME_PROCESSOR($DATA)
{
    // process the inpute data and divide it all, with "/" as the delimiter. If its a link then this sign is expected to be in the link and can
    // be used to divide it, but if its not a link just a text, the "explode" function will not throw error instead it creates an array of only
    // one index and assign our data into that index. 
    $PROCESSED_DATA = explode("/", $DATA);
    // after dividing into an array we need the numbers of indexes of this array.
    $PROCESSED_DATA_count = count($PROCESSED_DATA);
    // if the number is greater than zero this means our "explode" function did alot of work simply because our inpute data is a link, but if its
    // not, then our "explode" function didn't get much to do, hence we can safely conclude its not a link.
    if ($PROCESSED_DATA_count > 0) {
        return DOUBLE_EDGED_CHOPPER($PROCESSED_DATA[($PROCESSED_DATA_count - 1)], "@");
    } else {
        return DOUBLE_EDGED_CHOPPER($PROCESSED_DATA[0], "@");
    }
}











// FUNCTION TO HELP US EXTRACT THE FULL DOMAIN FROM A COMPLEXE URL. THIS FUNCTION ALSO EXTRACT SUBDOMAINS TOO AS PART OF THE FULL DOMAIN. CHECKOUT
// WORKING EXAMPLES BELLOW.
// echo EXTRACT_FULL_DOMAIN("http://example.com");  => outputs 'example.com'
// echo EXTRACT_FULL_DOMAIN("http://www.example.com");  => outputs 'example.com'
// echo EXTRACT_FULL_DOMAIN("http://mail.example.co.uk");  => outputs 'mail.example.co.uk'
function EXTRACT_FULL_DOMAIN($URL)
{
    $parseUrl = (substr(trim($URL), 0, 4) == "http") ? parse_url(trim($URL)) : parse_url(trim("http://" . $URL));;
    return LEFT_CHOPPER(trim($parseUrl['host'] ? $parseUrl['host'] : array_shift(explode('/', $parseUrl['path'], 2))), "www.");
}











// FUNCTION TO HELP US EXTRACT ONLY THE BASE DOMAIN FROM A COMPLEXE URL. THIS FUNCTION IGNORES SUBDOMAINS FROM ITS RESULTALT DATA AS SUBDOMAINS ARE
// NOT CONSIDERED AS PART OF A BASE DOMAIN. CHECKOUT WORKING EXAMPLES BELLOW.
// echo EXTRACT_BASE_DOMAIN("http://example.com");  => outputs 'example.com'
// echo EXTRACT_BASE_DOMAIN("http://www.example.com");  => outputs 'example.com'
// echo EXTRACT_BASE_DOMAIN("http://mail.example.co.uk");  => outputs 'example.co.uk'
function EXTRACT_BASE_DOMAIN($URL)
{
    $pieces = (substr(trim($URL), 0, 4) == "http") ? parse_url(trim($URL)) : parse_url(trim("http://" . $URL));
    $domain = isset($pieces['host']) ? $pieces['host'] : '';

    if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
        return $regs['domain'];
    }

    return false;
}














// A FUNCTION TO HELP US PROCESS A URL, EXTRACT THE BASE/FULL DOMAIN AWAY FROM THE URL AND ALSO SHORTEN THE URL WHEN IT EXCEEDS THE CHARACTER
// THRESHOLD LIMIT. 
// => SET "$DOMAIN_EXTRACTION_TYPE" PARAMETER TO "FULL" IF YOU WANT THE DOMAIN NAME EXTRACTION FROM INPUT URL TO ALLOW SUBDOMAINS
// ELSE SET TO "BASE" IF YOU WANT EXTRACTION TO BE STRICT AND TO ONLY EXTRACT THE BASE DOMAIN ONLY REGARDLESS OF THE PRESENCE OF A SUBDOMAIN.
// => USE THE "$CHARACTER_LIMIT" PARAMETER TO TELL THIS FUNCTION THE NUMBER OF CHARACTER TO COUNT BEFORE IT SHORTENS THE EXTRACTED DOMAIN. 
// => USE THE "$URL" PARAMETER TO INPUTE THE COMPLEXE URL YOU WANT TO PROCESS AND SEND TO THIS FUNCTION TO PROCESS IT.
function URL_PROCESSOR($URL, $CHARACTER_LIMIT, $DOMAIN_EXTRACTION_TYPE = "FULL")
{
    // Run a test to know the kind of domain name extraction we shall be performing for this function.
    if ($DOMAIN_EXTRACTION_TYPE === "FULL") {
        $DOMAIN_NAME_EXTRACT = EXTRACT_FULL_DOMAIN($URL);
    } else if ($DOMAIN_EXTRACTION_TYPE === "BASE") {
        $DOMAIN_NAME_EXTRACT = EXTRACT_BASE_DOMAIN($URL);
    }

    // After extracting our domain we now reconstruct the url and truncate the combinned character length should it exceeds the setted threshold
    // we return final output here.
    return truncator("www." . $DOMAIN_NAME_EXTRACT, $CHARACTER_LIMIT, "..");
}











/*  A function to help count the number of words in a string   */
function __String_Words_Counter($Operating_String)
{
    /*  Return Result  */
    return str_word_count($Operating_String, 0);
}










/* FUNCTION TO HELP US ENSURE THAT NUMBERS DO NOT EXCEED FOUR CHARACTERS AT A PARTICULAR TIME. */
function NUMBER_SHRINKER($NUMBER)
{
    /* SET THE ROUND UP SYSTEM TO BE UTILIZED IN THIS FUNCTION. SET TO EITHER "SYSTEM" OR "PHP" */
    $ROUNDING_SYSTEM = "SYSTEM";
    /* CONSTRUCT TO TEST THROUGH ALL POSSIBLE PHASES OF THE SUPPLIED NUMBER, IN A BID TO KNOW WHAT MATHEMATICAL OPERATION TO APPLY TO SHRINK THE NUMBER. */
    if ($NUMBER > "999999999999") {
        return Round_Up(($NUMBER / 1000000000000), 4, $ROUNDING_SYSTEM) . "T";
    } else if ($NUMBER > "999999999") {
        return Round_Up(($NUMBER / 1000000000), 4, $ROUNDING_SYSTEM) . "B";
    } else if ($NUMBER > "999999") {
        return Round_Up(($NUMBER / 1000000), 4, $ROUNDING_SYSTEM) . "M";
    } else if ($NUMBER > "999") {
        return Round_Up(($NUMBER / 1000), 4, $ROUNDING_SYSTEM) . "K";
    } else {
        return Round_Up($NUMBER, 4, $ROUNDING_SYSTEM);
    }
}













/*   A function to round up numbers   */
function Round_Up($number_to_round, $Round_Constant = 4, $OPERATOR = "SYSTEM")
{
    /* here the system check which way invoker will like the rounding operation to be done, using the data at the "$OPERATOR" parameter.  */
    if ($OPERATOR === "PHP") {
        /* if the control enters here it signifies that the invoker will like the rounding operation to be done, using PHP Method, so in a bid to accomplish this we first get the
            * FLOAT equivalent of the inpute data to be rounded.  */
        $number_to_round_FLOAT = floatval($number_to_round);
        /* the actual rounding is done here, and output data returned.  */
        return strval(round($number_to_round_FLOAT, $Round_Constant));
    } else {
        /* if the control enters here it signifies that the invoker will like the rounding operation to be done, using SYSTEMs Method, so in a bid to accomplish this we creat a 
            * variable to store the final result  */
        $Result = null;
        /* if the control gets here it mean it's a decimal value, and as such is splitted into an array for further analysis.  */
        $Array_Of_Numbers = explode(".", $number_to_round);
        /* we get the count of our array and use it to check if the supplied data is a decimal number type of data, if it is 
            * then the rounding up rule is applied, if there isn't then the value is left as it was.  */
        if (count($Array_Of_Numbers) == 2) {
            /* In a bid the perfect the operation of this function we use the '__String_To_Chars_Splitter' method of this class to split the decimal side
                * into characters, so we can safely extract only the first number in the decimal side of user inpute. note that this move is error safe.  */
            $first_decimal_side_character = __String_To_Chars_Splitter($Array_Of_Numbers[1])[0];
            /* function to check if the first number after the decimal point is above 4, if it is then, 1 is added to the whole number.   */
            if ($first_decimal_side_character > $Round_Constant) {
                $Result = $Array_Of_Numbers[0] + 1;
            } else {
                /* if the control gets in here in means that the first number after the decimal point is less tha 5, therefore we add the first number after the decimal to a 
                    * decimal result only if this number is greater than zero  */
                if ($first_decimal_side_character > 0) {
                    $Result = $Array_Of_Numbers[0] . "." . $first_decimal_side_character;
                } else {
                    $Result = $Array_Of_Numbers[0];
                }
            }
        } else {
            /*  if the control gets in here it mean that the number inputed is not a decimal in the first place, so the number is actually a whole number. */
            $Result = $number_to_round;
        }

        /* return our final result.  */
        return $Result;
    }
}











// In the financial sector, when rounding numbers to the nearest two decimal places, the common rounding method used is "round half up," also 
// known as "bankers' rounding." This method involves rounding numbers that are halfway between two possible rounded values (e.g., 0.005) to 
// the nearest even number. For example: If the number is 1.235, it would be rounded to 1.24 or If the number is 1.245, it would also be 
// rounded to 1.24. This method is considered fairer because it balances the rounding in both directions, reducing the overall bias that could 
// occur from always rounding up or down. 
function BANKERS_ROUNDING($number_to_round)
{
    return round($number_to_round, 2, PHP_ROUND_HALF_UP);
}









// If you want to round up regardless of the decimal portion being below or equal to 0.5, you can use the PHP_ROUND_HALF_UP rounding mode. This 
// mode rounds numbers up (towards positive infinity) when the fractional part is greater than or equal to 0.5. Forexample, if we have an inpute 
// of 3.2 it will be rounded "up" to 4; because PHP_ROUND_HALF_UP always rounds up when the decimal portion is greater than or equal to 0.5. 
// If you had used PHP_ROUND_HALF_DOWN, it would round down in this case.
function UPWARDS_ROUNDING($number_to_round)
{
    // process data
    $raw = explode(".", $number_to_round);
    // run a test to affirm the input number is not having decimal, if it does we simply extract the whole number part of the number and add one
    // to it in a bid to round it up, but if its a whole number we just return it without rounding.
    if (count($raw) > 1) {
        return $raw[0] + 1;
    } else {
        return $raw[0];
    }
}











/*  A function to help us divide a string to its individual build up characters.   */
function __String_To_Chars_Splitter($Operating_String, $case = NULL)
{
    /* here we use the $case variable of which it default entry is 'lower', we use it to automate the output string of this function, as to wether the
        * splits the string and return the output characters in array either as a complete lower case or higher case, or just forgets about case and use
        * the case on the string as that.  */
    if ($case === "lower") {
        return str_split(_Lower_Case($Operating_String));
    } else if ($case === "higher") {
        return str_split(_Upper_Case($Operating_String));
    } else if ($case === NULL) {
        return str_split(trim($Operating_String));
    }
}








/*  A function to help us convert a string to it upper case formate   */
function _Upper_Case($Operating_String)
{
    /*  Return Result  */
    return strtoupper(trim($Operating_String));
}











/*  A function to help us convert a string to it lower case formate   */
function _Lower_Case($Operating_String)
{
    /*  Return Result  */
    return strtolower(trim($Operating_String));
}











// A FUNCTION TO HELP US RE-FORMATE MYSQL TIMESTAMP DATE FORMATE INTO A FORM THAT IS READABLE
function MSQL_Timestamp_toHuman_Readable_Format($MSQL_Timestamp)
{
    /*  Return Result  */
    return date('F jS Y \a\t g:ia', strtotime($MSQL_Timestamp));
}











// A FUNCTION TO HELP US RE-FORMATE MYSQL TIMESTAMP DATE FORMATE INTO A FORM THAT IS READABLE
function MSQL_Timestamp_Pretifier($MSQL_Timestamp)
{
    return MSQL_Timestamp_toHuman_Readable_Format($MSQL_Timestamp);
}











// A FUNCTION TO HELP US RE-FORMATE MYSQL TIMESTAMP DATE FORMATE INTO A FORM THAT IS READABLE
function MSQL_Timestamp_Date_Only_Pretifier($MSQL_Timestamp)
{
    /*  Return Result  */
    return date('F jS Y', strtotime($MSQL_Timestamp));
}











// A FUNCTION TO HELP US RE-FORMATE MYSQL TIMESTAMP DATE FORMATE INTO A FORM THAT IS READABLE
function MSQL_Timestamp_Time_Only_Pretifier($MSQL_Timestamp)
{
    /*  Return Result  */
    return date('g:ia', strtotime($MSQL_Timestamp));
}











// A FUNCTION TO HELP US CONVERT AN INTEGER REPRESENTING MINUTES, THIS FUNCTION CONVERTS THIS MINUTES INTO A MORE READABLE FORMATE. I.E 150 WILL
// RETURN 2 HOURS 30 MINUTES.
function MINUTES_TO_HOURS_AND_MINUTES_CONVERTER($TIME_IN_MINUTES)
{
    // CONVERT THE TIME IN MINUTES TO HOURS
    $HOURS = intdiv($TIME_IN_MINUTES, 60);
    // FIGURE OUT THE REMAINDER MINUTES, THATS IF THERE BE.
    $MINUTES = ($TIME_IN_MINUTES % 60);

    // SET UP A CLAUSE TO HANDLE SCENEROES WHEREBY THE MINUTES INTEGER IS GREATER OR EQUALS TO 60. SINCE IT TAKES 60 MINUTES TO MAKE ONE HOUR, 60
    // IN THIS CONTEXT BECOMES A CONSTANT THAT WE USE TO PRODUCE A NEW, HUMAN READABLE OUTPUTE
    if ($TIME_IN_MINUTES >= 60) {
        // WE FURTHER RUN TESTS TO AFFIRM IF WE NEED TO ADD MINUTES STRING TO THE MIX OF THIS FUNCTIONS FINAL OUTPUT
        if ($MINUTES > 0) {
            $x = ($HOURS > 1) ? $HOURS . " Hours " : $HOURS . " Hour ";
            $y = ($MINUTES > 1) ? $MINUTES . " Minutes" : $MINUTES . " Minute";
            return $x . $y;
        } else {
            return ($HOURS > 1) ? $HOURS . " Hours" : $HOURS . " Hour";
        }
    } else {
        return ($MINUTES > 1) ? $MINUTES . " Minutes" : $MINUTES . " Minute";
    }
}












/* FUNCTION TO HELP TEST A DATA, IF ITS NUMERIC OR NOT. FUNCTION RETURNS TRUE IF DATA IS AN INTEGER. */
function INTEGER_DATATYPE_TESTER($DATA)
{
    /* Here we test to see if the key value is of an integer data type, if it is then it only mean that the array is not an assosiative type.  */
    if (is_numeric($DATA)) {
        return TRUE;
    } else {
        return FALSE;
    }
}






/* FUNCTION TO HELP DETECT IF A SUPPLIED PHONE NUMBER IS NIGERIAN OR INTERNATIONAL (REGARDLESS OF HOW ITS INPUTTED), AND THEN HELP PROPERLY FORMATE THE NUMBER. */
function NIGERIA_AND_INTERNATIONAL_NUMBER_DETECTOR_AND_FORMATTER($PHONE_NUMBER)
{
    /* To start detecting if number is nigerian, we split the number into characters. */
    $SMASHED_CHARACTERS = __String_To_Chars_Splitter($PHONE_NUMBER);
    /* Now we check if the number is at least greater than 4, if its not then its not nigerian number. */
    if (count($SMASHED_CHARACTERS) > 4) {
        /* In a bid to do the first check which is to affirm its nigerian we first pick the first three digits of the supplied phone number and concate it as a single string. */
        $FIRST_THREE_DIGITS = ($SMASHED_CHARACTERS[0] . $SMASHED_CHARACTERS[1] . $SMASHED_CHARACTERS[2]);
        /* Now we check this entries, if the "$FIRST_THREE_DIGITS" data matches, any of this entries then we are certain its a nigerian number, else we carry on to other tests
             * to still affirm if phone number is nigerian.  */
        if (($FIRST_THREE_DIGITS == "080") || ($FIRST_THREE_DIGITS == "081") || ($FIRST_THREE_DIGITS == "070") || ($FIRST_THREE_DIGITS == "071") ||
            ($FIRST_THREE_DIGITS == "090") || ($FIRST_THREE_DIGITS == "091")
        ) {
            /* Control in here means there is a match and that the supplied phone number is certainly nigerian. Therefore we employ the service of the php inbuilt "array_shift" 
                 * function against the "$SMASHED_CHARACTERS" array, to help us remove the first index away and re-align the index of the "$SMASHED_CHARACTERS" array. we doing this 
                 * to prepare the phone number for reformating, since its detected that this number is nigerian, in a bid to format it properly we first delet the zero lagging infront.  */
            array_shift($SMASHED_CHARACTERS);
            /* to complete the formating we concate "+234" to the string we already have, and hence return the final output result.  */
            return "+234" . (implode("", $SMASHED_CHARACTERS));
        } else if ($FIRST_THREE_DIGITS == "234") {
            /* Control in here means there is a match and that the supplied phone number is certainly nigerian. Since client is a little careless with the phone number formatting
                 * this function have this clause setted up to redeem the phone number proper fpormatting even despit this carelessnes. So now what is left to be added to complete the
                 * proper formating of the phone number is "+", therefore we simply add that and return the final output result.   */
            return "+" . $PHONE_NUMBER;
        } else if ($FIRST_THREE_DIGITS . $SMASHED_CHARACTERS[3] == "+234") {
            /* Control in here means there is a match and that the supplied phone number is certainly nigerian. We had to add one more index from the "$SMASHED_CHARACTERS" array 
                 * to the "$FIRST_THREE_DIGITS" data, to take the over-all digits to for, fear not this stunt wont lead to error because in the first instance control wont get here if
                 * the count of the "$SMASHED_CHARACTERS" array doesn't surpass four. This test is carried out to test if user inpute phone number is properly formatted, and since
                 * control is in here it means so. Therefore we are still certain phone number is nigerian and no need to add anything as we just return the final output result.   */
            return $PHONE_NUMBER;
        } else {
            /* AT THIS JUNCTION WE ARE CERTAIN THE NUMBER IS NOT NIGERIAN, THEREFORE WE SHALL BE DEPLOYING OTHER TACTIC TO IDENTIFY ONLY THE POPULAR COUNTRIES, therefore we 
                 * launch the service of the "INTERNATIONAL_PHONE_NUMBER_FORMATTER" to finish things up. */
            return INTERNATIONAL_PHONE_NUMBER_FORMATTER($PHONE_NUMBER);
        }
    } else {
        /* AT THIS JUNCTION WE ARE CERTAIN THE NUMBER IS NOT NIGERIAN, THEREFORE WE DEPLOY THE SERVICE OF THE "INTERNATIONAL_PHONE_NUMBER_FORMATTER" FUNCTION TO HANDLE THIS. */
        return INTERNATIONAL_PHONE_NUMBER_FORMATTER($PHONE_NUMBER);
    }
}











/* This function will format international (10+ digit), non-international (10 digit) or old school (7 digit) phone numbers. Any numbers other than 10+, 10 or 7 digits will remain 
     * unformatted. For better precision use "https://github.com/google/libphonenumber" */
function INTERNATIONAL_PHONE_NUMBER_FORMATTER($phoneNumber)
{
    $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

    if (strlen($phoneNumber) > 10) {
        $countryCode = substr($phoneNumber, 0, strlen($phoneNumber) - 10);
        $areaCode = substr($phoneNumber, -10, 3);
        $nextThree = substr($phoneNumber, -7, 3);
        $lastFour = substr($phoneNumber, -4, 4);

        $phoneNumber = '+' . $countryCode . ' (' . $areaCode . ') ' . $nextThree . '-' . $lastFour;
    } else if (strlen($phoneNumber) == 10) {
        $areaCode = substr($phoneNumber, 0, 3);
        $nextThree = substr($phoneNumber, 3, 3);
        $lastFour = substr($phoneNumber, 6, 4);

        $phoneNumber = '(' . $areaCode . ') ' . $nextThree . '-' . $lastFour;
    } else if (strlen($phoneNumber) == 7) {
        $nextThree = substr($phoneNumber, 0, 3);
        $lastFour = substr($phoneNumber, 3, 4);

        $phoneNumber = $nextThree . '-' . $lastFour;
    }

    return $phoneNumber;
}















/* A FUNCTION TO GIVE US A FULL WELL FORMATED DATE. I.E => "Friday, 28th of August 2020.  10:57:05 am". THE "$TIME_ZONE" PARAMETER OF THIS FUNCTION IS USED TO SET THE 
     * TIMEZONE THAT THIS FUNCTION SHOULD OPERAT WITH, WHILE THE "$DATE_WITH_TIME" PARAMETER OF THIS FUNCTION IS USED TO DECIDE IF THE CURRENT TIME SHOULD BE ADDED TO THE 
     * FULL DATE. */
function get_Formated_Date($DATE_WITH_TIME = FALSE, $TIME_ZONE = 'Europe/London')
{
    /* get timezone. */
    date_default_timezone_set($TIME_ZONE);
    /* get the current week-day, according to time zone. */
    $current_week_day = date("l");
    /* use this libraries "Month_Formater" function to get the current and int-to-string converted month information, according to timezone. check the documentations of 
         * this function to understand more.  */
    $current_month = Month_Formater($TIME_ZONE);
    /* use this libraries "Day_Formater" function to get the current and int-to-string converted Day information, according to timezone. check the documentations of 
         * this function to understand more.  */
    $current_day = Day_Formater($TIME_ZONE);
    /* get the current year, according to time zone. */
    $current_year = date("Y");
    /* Here we check to see if user needs only the formated full date or if he wants the time to be included in the final result.  */
    if ($DATE_WITH_TIME === TRUE) {
        /* get the full current time, according to time zone. */
        $current_time = date("h:i:s a");
        /* formate the date and time, it should look something like this format => "Friday, 28th of August 2020.  10:57:05 am" */
        return $current_week_day . ", " . $current_day . " of " . $current_month . " " . $current_year . " " . $current_time;
    } else {
        /* formate the date and time, it should look something like this format => "Friday, 28th of August 2020.  10:57:05 am" */
        return $current_week_day . ", " . $current_day . " of " . $current_month . " " . $current_year;
    }
}











function get_Formated_Date_REBRANDED($DATE_WITH_TIME = FALSE, $TIME_ZONE = 'Europe/London')
{
    /* get timezone. */
    date_default_timezone_set($TIME_ZONE);
    /* use this libraries "Month_Formater" function to get the current and int-to-string converted month information, according to timezone. check the documentations of 
         * this function to understand more.  */
    $current_month = Month_Formater($TIME_ZONE);
    /* use this libraries "Day_Formater" function to get the current and int-to-string converted Day information, according to timezone. check the documentations of 
         * this function to understand more.  */
    $current_day = Day_Formater($TIME_ZONE);
    /* get the current year, according to time zone. */
    $current_year = date("Y");
    /* Here we check to see if user needs only the formated full date or if he wants the time to be included in the final result.  */
    if ($DATE_WITH_TIME === true) {
        /* get the full current time, according to time zone. */
        $current_time = date("h:i:s a");
        /* formate the date and time, it should look something like this format => "Friday, 28th of August 2020.  10:57:05 am" */
        return $current_day . " of " . $current_month . " " . $current_year . " " . $current_time;
    } else {
        /* formate the date and time, it should look something like this format => "Friday, 28th of August 2020.  10:57:05 am" */
        return $current_day . " of " . $current_month . " " . $current_year;
    }
}












/* A FUNCTION TO HELP US GET THE CURRENT MONTH ACCORDING TO THE SUPPLIED TIMEZONE, THEN GO ALL THE WAY TO INTERPRETE THE INTEGER MONTH DATA TO HUMAN LANGUAGE. */
function Month_Formater($TIME_ZONE)
{
    /* get timezone. */
    date_default_timezone_set($TIME_ZONE);
    /* get the current month integer data in accordance with the supplied timezone. */
    $current_month = date("m");
    /* Here we create an array of which contains the full string of the months in human language, arranged in an ascending order. Note that the integer index of this array
         * starts from zero, and we shall be using this index to compare and test with the integer data returned by the date function, by processing this date function integer
         * and then subtracting one from it and then using it to index this array to get the human language equavalent of the integer data returned by the date function. Also
         * note that this our approach would not lead to any error as any integer data to be returned by the date function are all between (1-12) and the date function is invoked
         * inside this function. */
    $TESTING_TUBE = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
    /* Right here we firstly subtracted one from the "$current_month" variable thats because our "$TESTING_TUBE" array counting starts from zero and therefore since we are
         * using its index to represent the integer month value from our date function, it is only proper for us to always subtract one from it just so everything can be leveked,
         * secondly we chop of the zero before the integer data inside our "$current_month" variable just so we can have an indexable integer to use, offcourse we know the integer
         * data returned from the date function always have a zero infront of it. finally we use our processed integer data to index the "$TESTING_TUBE" array, just to get the
         * human language equivalent of the integer data.   */
    //return $TESTING_TUBE[ ($this->Text_in_String_REPLACER("0", "", ($current_month - 1))) ];
    return $TESTING_TUBE[($current_month - 1)];
}















/* A FUNCTION TO HELP US GET THE CURRENT DATE ACCORDING TO THE SUPPLIED TIMEZONE, THEN GO ALL THE WAY TO INTERPRETE THE INTEGER DATE DATA TO HUMAN LANGUAGE. */
function Day_Formater($TIME_ZONE)
{
    /* get timezone. */
    date_default_timezone_set($TIME_ZONE);
    /* get the current date integer data in accordance with the supplied timezone. */
    $current_day = date("d");
    /* here we test the integer output of our php date function, and we use this sets of constructs to convert this integer outputes to human language equivalent. */
    if ($current_day === "01") {
        $day = "1st";
    } else if ($current_day === "02") {
        $day = "2nd";
    } else if ($current_day === "03") {
        $day = "3rd";
    } else {
        /* In a bid to ensure that the day string doesn't have a zero infront, we use the "__String_To_Chars_Splitter" function to split the string into characters in 
             * array and we get only the first character of the date string and check if this character is equals to zero. */
        if (__String_To_Chars_Splitter($current_day)[0] === "0") {
            /* if the control gets here it mean this first character is zero, therefore we use the "" function to chop of this first character. */
            $current_day = __STRING_DIVIDER($current_day, 1);
        }

        /* finally we compile the final result. */
        $day = $current_day . "th";
    }

    /* We return the final result of this function. */
    return $day;
}








/* A function to help divide a string to the right side using a supplied integer which denotes the indexes of the characters inside the string to
     * divide. note that this integer counting starts from zero. therefore this function Returns a part of a string. lets see an example here; i.e
     * [  echo substr("Hello world",6);  ] <-------- this example returns 'world' as the divided to the right path string.  */
function __STRING_DIVIDER($MAIN_String, $BREAKER)
{
    /*  Return Result  */
    return substr($MAIN_String, $BREAKER);
}








/* A FUNCTION TO HELP US GET THE CURRENT DATE ACCORDING TO THE SUPPLIED TIMEZONE. */
function UNFORMATED_DATE($DATE_WITH_TIME = TRUE, $TIME_ZONE = 'Europe/London')
{
    /* get timezone. */
    date_default_timezone_set($TIME_ZONE);
    /* Here we check to see if user needs only full date or if he wants the time to be included in the final result.  */
    if ($DATE_WITH_TIME === TRUE) {
        return date('d-m-Y H:i:s a');
    } else {
        return date('d-m-Y');
    }
}






/* A FUNCTION TO HELP US GET CURRENT DATE AND TIME, IN MYSQL TIMESTAMP DATE FORMATE. THE PM/AM IS OMMITTED FOR THIS FUNCTION.  */
function MYSQL_DATE_FORMATE($DATE_WITH_TIME = true, $TIME_ZONE = 'Europe/London')
{
    /* get timezone. */
    date_default_timezone_set($TIME_ZONE);
    /* Here we check to see if user needs only full date or if he wants the time to be included in the final result.  */
    if ($DATE_WITH_TIME === true) {
        return date('Y-m-d H:i:s');
    } else {
        return date('Y-m-d');
    }
}









/*  FUNCTION TO HELP US CHECK IF AN ARRAY HAS DUPLICATES IN IT. THIS FUNCTION RETURNS TRUE OF THE ARRAY HAS DUPLICATE VALUES AND RETURNS FALSE OTHERWISE. AND INCASE IT RETURNS
     * TRUE TO INDICATE THAT THE SUPPLIED ARRAY HAS DUPLICATE VALUES, IT GOES ALL THE WAY TO ALSO RETURN A NEW VERSION OF THE INPUTE ARRAY WITHOUT DUPLICATE VALUES.  */
function DUPLICATES_INARRAY_DETECTOR(&$ARRAY)
{
    /* use the "DUPLICATE_IN_ARRAY_CHOPPER" function to create a new version of the inpute array, only that this one has all duplicate values merged as one. */
    $NEW_ARRAY = DUPLICATE_IN_ARRAY_CHOPPER($ARRAY);
    /* Here we test the size of both old and new, if there is a change in the size of the new array, to cause an inbalance then this indicates a duplicate value in the inpute 
         * array. */
    if (count($NEW_ARRAY) != count($ARRAY)) {
        /* since we are certain at this point that the input array has duplicates, its important we send the newly created array back to the invoking function. */
        $ARRAY = $NEW_ARRAY;
        /* now we return true to indicate that the inpute array has duplicates. */
        return TRUE;
    } else {
        /* we return false to indicate that the inpute array has no duplicates. */
        return FALSE;
    }
}






/* FUNCTION TO REMOVE DUPLICATE VALUES FROM AN ARRAY AND ALSO RE-ORDER THE SEQUENCE OF THE INDEXES OF THE ARRAY.  */
function DUPLICATE_IN_ARRAY_CHOPPER($ARRAY_TO_WORK_ON)
{
    /* Here we use the "array_unique" PHP function to remove any sort of duplicates inside the values of the inpute array, this PHP function will only
        * allow the first occurence of this value once it comes in contact with another it scraps off both this value and its index, which is not totally
        * ideal for a more professional job, it should have re-ordered the indexes incase this array indexes would be highly needed inside a loop
        * therefore we have to patch this ourselves by intronducing the "array_values" PHP function at to help us re-order the indexes of the new array 
        * therefore we quickly do that, and return the final array result.  */
    return array_values((array_unique($ARRAY_TO_WORK_ON)));
}





/* this function helps us to compare two strings on a binary level and then it affirm if they are the same regardless of the CASE[UPPER CASE OR LOWER
     * CASE]. This function is best used to compare strings when you have to do thousands of string compare on a single programme runtime, this function
     * is binary safe and does that operation quickest. this function returns true if the two strings are same and it returns false if they are not, 
     * remember it compares regardless of the case of the string, so its case insensitive. but if the third parameter of this function is used them it
     * signifies that the user wants more from this function that is the function would return zero['0'] if the two strings are the saame, it will return
     * negative values/integers if the first string is less than the second string, and positive values/integers if the first string is greater than the 
     * second[string mixed with integers] it simply subtracts the first string integer from the second string integer binary wise to achieve this fit.  */
function __BINARY_SAFE_STRING_COMPARE($FIRST_STRING, $SECOND_STRING, $OPERATION_TYPE = "DONT STRETCH")
{
    /* Use PHP binary safe comparator engine['strcasecmp' php function] to compare our two strings, read more about the operation of the strcasecmp'
         * to understand more.  */
    $Compare_Result = strcasecmp($FIRST_STRING, $SECOND_STRING);
    /* here we try to affirm if user is using the third parameter to tell how this function should operate. */
    if ($OPERATION_TYPE == "DONT STRETCH") {
        /* if control gets in here it only mean user does not want this function to do other things but only to compare this two strings alone, and return
             * true if they are equal else false if they are not.  */
        if ($Compare_Result == 0) {
            return TRUE;
        } else {
            return FALSE;
        }
    } else if ($OPERATION_TYPE == "STRETCH") {
        /* if control gets in here it only mean user does wants this function to do other things, apart from comparing the two inputed strings */
        return $Compare_Result;
    }
}








/*  THIS FUNCTION QUICKLY TESTS AN ARRAY AND TELLS US IF ITS AN ASSOSIATIVE ARRAY OR NOT.   */
function ASSOSIATIVE__Array_Tester($Testing_Array)
{
    /* variable to be used to return the final result.   */
    $Final_Result = null;
    /*  count the number of values stored in array.   */
    $array_count = count($Testing_Array);
    /*  at this point we check to confirm if the array at least has value in it.   */
    if ($array_count > 0) {
        /* now we try to get the assosiative_index_name of the first value.[it doesn't matter if the testing array is assosiative or not]  */
        $Array_First_Value_Key_Name = array_keys($Testing_Array)[0];
        /* now we test to see if the key value is of an integer data type, if it is then it only mean that the array is not an assosiative type.  */
        if (filter_var($Array_First_Value_Key_Name, FILTER_VALIDATE_INT) === 0 || !filter_var($Array_First_Value_Key_Name, FILTER_VALIDATE_INT) === false) {
            /* if the control gets in here it mean that the array is not an assosiative array.   */
            $Final_Result = "INTEGRAL";
        } else {
            /* if the control gets in here it mean that the array is an assosiative array.   */
            $Final_Result = "ASSOCIATIVE";
        }
    }
    /*  return the final result.  */
    return $Final_Result;
}











/* FUNCTION TO HELP US AFFIRM IF A DATA IS EITHER AN ARRAY OR NOT. IT RETURNS TRUE IF THE INPUT DATA IS AN ARRAY AND FALSE IF IT IS NOT. */
function ARRAY_CONFIRMER($DATA)
{
    /* we is php "is_array" function to dynamically check the nature of the inputed data wether it is an array or not.  */
    if (is_array($DATA)) {
        /* we return true if it is an array. */
        return TRUE;
    } else {
        /* we return false to signify it is not an arrya. */
        return FALSE;
    }
}









/*  THIS FUNCTION HELPS INVOKER SEARCH AN ARRAY FOR A GIVEN VALUE, AND THEN RETURNS 'PRESENT' AS 'confirmation_string' IF VALUE IS FOUND, ALSO RETURNS
     *  BOTH THE INDEX AND VALUE FOUND ALONG. THIS FUNCTION HAS BEEN BUILT WITH AN INTERNAL TESTER TO TEST THE TYPE OF ARRAY SUPPLIED INTO IT, AND COMPLETE
     *  THE REST OF THE OPERATION DEPENDING ON THE TESTERS RESULT. USER CAN ALSO HELP BY USING THE[$Array_Type = "AUTOMATIC"] PARAMETER TO SPECIFY THE NATURE
     *  OF THE ARRAY SUPPLIED TO THIS FUNCTION, AS THIS ACT CAN HELP SPEED UP THE TIME OF OPERATION COMPLETION. LASTELY WHEN THE THIRD PARAMETER IS SETTED
     *  TO 'PHP' THIS FUNCTION WOULD USE THE BINARY PHP ARRAY SEARCHER FUNCTION TO ACHIEVE THESAME RESULT BUT THE DOWNSIDE OF THIS APPROACH IS THAT PHP
     *  PERFORMS THIS SEARCH CASE SENSITIVELY THEREFORE THE KEYWORD MUST MATCH THE DATA INSIDE THE ARRAY CASE WISE THIS DOWNSIDE AFFECTS THE PRECISION OF
     *  THE ARRAY SEACHING OPERATION BUT THE POSITIVE SIDE OF USING THIS IS THAT ITS THE QUICKEST EVER BECAUSE ITS BINARY SAFE ITS DONE ON THE BINARY STREAM
     *  SO ITS THE FASTEST COMPARED TO OURS, BUT WHEN USING THIS SEARCHER FOR A BIG DATA SO THAT THE OPERATION DOES NOT TIME OUT ENDEAVORE TO USE THE PHP
     *  OPTION.  */
function __Array_Searcher($Searcheable_Array, $Keyword, $Array_Type = "AUTOMATIC")
{
    /*  array to store result   */
    $Result = array();
    $Result['confirmation_string'] = "ABSENT";
    $Result['Keyword_value_in_seacheable_array'] = null;
    $Result['Keyword_index_in_seacheable_array'] = null;

    /* AFFIRM USER SUPPLIED AN ARRAY. */
    if (ARRAY_CONFIRMER($Searcheable_Array) === TRUE) {
        /*  we use this section of code to tell the construct below what to do.    */
        if ($Array_Type === "AUTOMATIC") {
            $teller = ASSOSIATIVE__Array_Tester($Searcheable_Array);
        } else {
            $teller = $Array_Type;
        }

        /* right here we test to see if the array is not the associative type.  */
        if ($teller === "INTEGRAL") {
            /*  loop we used to run the array containing verb indexes against the idea keyword.    */
            for ($x = 0; $x < count($Searcheable_Array); $x++) {
                /* construct to make sure that we have a match, send back a confirmation string alongside the indexe of the keyword in the searcheable array. */
                //if(_Upper_Case($Searcheable_Array[$x]) == _Upper_Case($Keyword)){
                /* WE USE THE "__BINARY_SAFE_STRING_COMPARE" TO COMPARE OUR STRINGS BECAUSE ITS DONE IN BYTES MODE AND ITS PRETTY FAST AND CASE INSENSITIVE. */
                if (__BINARY_SAFE_STRING_COMPARE(trim($Searcheable_Array[$x]), trim($Keyword)) === TRUE) {
                    /*  confirm the presence of the keyword in the seacheable array.   */
                    $Result['confirmation_string'] = "PRESENT";
                    /*  return the value of the keyword in the searcheable array.   */
                    $Result['Keyword_value_in_seacheable_array'] = $Searcheable_Array[$x];
                    /*  return the indexe of the keyword in the searcheable array.  */
                    $Result['Keyword_index_in_seacheable_array'] = $x;
                    /*  break the loop immediately our match is found else if our match is found in time and our loop still has more to rotate it will 
                         *  surely finish even untop of the fact that we have found our match, that is surely a complete wast of processor time, 
                         *  where as it will not enter this 'if construct' more than once. so we break the loop at this point to save processor time.     */
                    break;
                }
            }
        } else if ($teller === "ASSOCIATIVE") {
            /*  loop we used to run the array containing verb indexes against the idea keyword.    */
            for ($x = 0; $x < count($Searcheable_Array); $x++) {
                /* construct to make sure that we have a match, send back a confirmation string alongside the indexe of the keyword in the searcheable array. */
                //if(_Upper_Case(array_values($Searcheable_Array)[$x]) == _Upper_Case($Keyword)){
                /* WE USE THE "__BINARY_SAFE_STRING_COMPARE" TO COMPARE OUR STRINGS BECAUSE ITS DONE IN BYTES MODE AND ITS PRETTY FAST AND CASE INSENSITIVE. */
                if (__BINARY_SAFE_STRING_COMPARE(trim((array_values($Searcheable_Array)[$x])), trim($Keyword)) === TRUE) {
                    /*  confirm the presence of the keyword in the seacheable array.   */
                    $Result['confirmation_string'] = "PRESENT";
                    /*  return the value of the keyword in the searcheable array.   */
                    $Result['Keyword_value_in_seacheable_array'] = array_values($Searcheable_Array)[$x];
                    /*  return the indexe of the keyword in the searcheable array.  */
                    $Result['Keyword_index_in_seacheable_array'] = array_keys($Searcheable_Array)[$x];
                    /*  break the loop immediately our match is found else if our match is found in time and our loop still has more to rotate it will 
                         *  surely finish even untop of the fact that we have found our match, that is surely a complete wast of processor time, 
                         *  where as it will not enter this 'if construct' more than once. so we break the loop at this point to save processor time.     */
                    break;
                }
            }
        } else if ($teller === "PHP") {
            /* use php inbuilt binary searcher to search our array for the keyword. */
            $Search_Result = array_search(trim($Keyword), $Searcheable_Array);
            /* we affirm if the search operation was successful */
            if ($Search_Result) {
                /*  confirm the presence of the keyword in the seacheable array.   */
                $Result['confirmation_string'] = "PRESENT";
                /*  return the value of the keyword in the searcheable array.   */
                $Result['Keyword_value_in_seacheable_array'] = $Keyword;
                /*  return the indexe of the keyword in the searcheable array.  */
                $Result['Keyword_index_in_seacheable_array'] = $Search_Result;
            }
        }
    }

    /*  return the result.  */
    return $Result;
}








/* FUNCTION TO DELETE A FILE FROM A GIVEN REAL PATH TO THE FILE TO BE DELETED. E.G C://HOLE/BOOKS/DASH.JPG   */
function FILE_DELETER($filename)
{
    /* check if the file real path is writable and if its exist and actually points to a file. */
    if ((is_writable($filename)) && (file_exists($filename))) {
        /* control in here signifies that the file real path is writable and points to a file, therefore we delete the file the path is pointing to.  */
        @unlink($filename);
        /* we return true indicating to subsequent functions that delete operations was a success. */
        return true;
    } else {
        /* control in here signifies that the file real path is not writable and does not point to a file, therefore we return true indicating to subsequent functions 
             * that delete operations failed. */
        return true;
    }
}








// A FUNCTION TO LOOK INTO THE CONTENTS OF AN ARRAY (INTEGER/ASSOCIATIVE ARRAY TYPE), AND REMOVE ALL INDEXES WITH EMPTY/NULL/UNDEFINED VALUES
function EMPTY_ARRAY_VALUE_FILTER($INPUT_ARRAY)
{
    // Loop through the array
    foreach ($INPUT_ARRAY as $key => $value) {
        // Check if the value is empty
        if (empty(trim($value))) {
            // If it's empty, remove the index from the array
            unset($INPUT_ARRAY[$key]);
        }
    }

    // return the array
    return $INPUT_ARRAY;
}











/* FUNCTION TO HELP US USE THE TOTAL NUMBER OF ROWS RETURNED BY THE DATABASE TO CALCULATE THE TOTAL NUMBER OF PAGES THAT WE WILL BE NEEDING TO 
     * DISPLAY ALL OF DATABASE DATA'S.   */
function TOTAL_PAGES_NUMBER_GETTER($number_Of_Rows, $PAGINATION_DIVISION)
{
    $tester = $number_Of_Rows / $PAGINATION_DIVISION;
    $data = explode(".", $tester);
    if (count($data) == 2) {
        /* if the control gets in here it mean that it was not divided evenly meaning that we might have less than the number we expect 
                * for a single page that is if our "$number_Of_Rows" is less than the "config_item('PAGINATION_DIVISION')" or it might be 
                * higher but all the same all we need to do is to add one and return. */
        return ($data[0] + 1);
    } else {
        /* if the control gets in here it mean that it was divided evenly therefore we just return only the whole number. */
        return $data[0];
    }
}












// Helper function to format individual numbers
function format_number($number, $OPERATION_TYPE)
{
    // Remove the negative sign if present
    $number = abs($number);

    if ($OPERATION_TYPE === "PHP") {
        return number_format($number, 0, '.', ',');
    } else if ($OPERATION_TYPE === "CUSTOM") {
        $WHOLE_PRICE = explode(".", $number)[0];
        $splitted_numbers_INARRAY = str_split(trim($WHOLE_PRICE));
        $splitted_numbers_INARRAY_count = count($splitted_numbers_INARRAY);

        if ($splitted_numbers_INARRAY_count > 3) {
            if ($splitted_numbers_INARRAY_count == 4) {
                return $splitted_numbers_INARRAY[0] . "," . $splitted_numbers_INARRAY[1] . $splitted_numbers_INARRAY[2] . $splitted_numbers_INARRAY[3];
            } else if ($splitted_numbers_INARRAY_count == 5) {
                return $splitted_numbers_INARRAY[0] . $splitted_numbers_INARRAY[1] . "," . $splitted_numbers_INARRAY[2] . $splitted_numbers_INARRAY[3]
                    . $splitted_numbers_INARRAY[4];
            } else if ($splitted_numbers_INARRAY_count == 6) {
                return $splitted_numbers_INARRAY[0] . $splitted_numbers_INARRAY[1] . $splitted_numbers_INARRAY[2] . "," . $splitted_numbers_INARRAY[3]
                    . $splitted_numbers_INARRAY[4] . $splitted_numbers_INARRAY[5];
            } else if ($splitted_numbers_INARRAY_count == 7) {
                return $splitted_numbers_INARRAY[0] . "," . $splitted_numbers_INARRAY[1] . $splitted_numbers_INARRAY[2] . $splitted_numbers_INARRAY[3] . ","
                    . $splitted_numbers_INARRAY[4] . $splitted_numbers_INARRAY[5] . $splitted_numbers_INARRAY[6];
            } else if ($splitted_numbers_INARRAY_count == 8) {
                return $splitted_numbers_INARRAY[0] . $splitted_numbers_INARRAY[1] . "," . $splitted_numbers_INARRAY[2] . $splitted_numbers_INARRAY[3]
                    . $splitted_numbers_INARRAY[4] . "," . $splitted_numbers_INARRAY[5] . $splitted_numbers_INARRAY[6] . $splitted_numbers_INARRAY[7];
            } else if ($splitted_numbers_INARRAY_count == 9) {
                return $splitted_numbers_INARRAY[0] . $splitted_numbers_INARRAY[1] . $splitted_numbers_INARRAY[2] . "," . $splitted_numbers_INARRAY[3]
                    . $splitted_numbers_INARRAY[4] . $splitted_numbers_INARRAY[5] . "," . $splitted_numbers_INARRAY[6] . $splitted_numbers_INARRAY[7]
                    . $splitted_numbers_INARRAY[8];
            } else if ($splitted_numbers_INARRAY_count == 10) {
                return $splitted_numbers_INARRAY[0] . "," . $splitted_numbers_INARRAY[1] . $splitted_numbers_INARRAY[2] . $splitted_numbers_INARRAY[3] . ","
                    . $splitted_numbers_INARRAY[4] . $splitted_numbers_INARRAY[5] . $splitted_numbers_INARRAY[6] . "," . $splitted_numbers_INARRAY[7]
                    . $splitted_numbers_INARRAY[8] . $splitted_numbers_INARRAY[9];
            } else if ($splitted_numbers_INARRAY_count == 11) {
                return $splitted_numbers_INARRAY[0] . "," . $splitted_numbers_INARRAY[1] . $splitted_numbers_INARRAY[2] . $splitted_numbers_INARRAY[3] . ","
                    . $splitted_numbers_INARRAY[4] . $splitted_numbers_INARRAY[5] . $splitted_numbers_INARRAY[6] . "," . $splitted_numbers_INARRAY[7]
                    . $splitted_numbers_INARRAY[8] . $splitted_numbers_INARRAY[9] . $splitted_numbers_INARRAY[10];
            } else if ($splitted_numbers_INARRAY_count == 12) {
                return $splitted_numbers_INARRAY[0] . $splitted_numbers_INARRAY[1] . $splitted_numbers_INARRAY[2] . "," . $splitted_numbers_INARRAY[3]
                    . $splitted_numbers_INARRAY[4] . $splitted_numbers_INARRAY[5] . "," . $splitted_numbers_INARRAY[6] . $splitted_numbers_INARRAY[7]
                    . $splitted_numbers_INARRAY[8] . "," . $splitted_numbers_INARRAY[9] . $splitted_numbers_INARRAY[10] . $splitted_numbers_INARRAY[11];
            } else if ($splitted_numbers_INARRAY_count == 13) {
                return $splitted_numbers_INARRAY[0] . "," . $splitted_numbers_INARRAY[1] . $splitted_numbers_INARRAY[2] . $splitted_numbers_INARRAY[3] . ","
                    . $splitted_numbers_INARRAY[4] . $splitted_numbers_INARRAY[5] . $splitted_numbers_INARRAY[6] . "," . $splitted_numbers_INARRAY[7]
                    . $splitted_numbers_INARRAY[8] . $splitted_numbers_INARRAY[9] . "," . $splitted_numbers_INARRAY[10] . $splitted_numbers_INARRAY[11]
                    . $splitted_numbers_INARRAY[12];
            }
        } else {
            return $number;
        }
    }
}

// Main function to format the price
function PRICE_FORMATTER($PRICE, $OPERATION_TYPE = "CUSTOM")
{
    // Check if the price is a range
    if (strpos($PRICE, '-') !== false) {
        // Split the range into min and max
        list($minPrice, $maxPrice) = explode('-', $PRICE);

        // Format both min and max prices
        $formattedMin = format_number($minPrice, $OPERATION_TYPE);
        $formattedMax = format_number($maxPrice, $OPERATION_TYPE);

        // Return the formatted range with currency sign
        return $formattedMin . " - " . config('app.CURRENCY_SIGN') . $formattedMax;
    } else {
        // Format the single price
        return format_number($PRICE, $OPERATION_TYPE);
    }
}





















/* A function to find the number equivalent of the percentage of a parent number. all number supplied to this function should be integers, it 
     * returns the result as integer aswell. take for example the 20% of 500 is 100, to use this function to know the 20% of 500, 500 will go into 
     * the "$Parent_Number" parameter, while 20 will go into the "$Percentage" parameter, hence the function would return 100. */
function Percentage_To_Number_Equivalent($Parent_Number, $Percentage)
{
    /* perform operation smoothly and quickly here.   */
    return (($Parent_Number / 100) * $Percentage);
}














/* A function to find the Percentage equivalent of a Percentage Value Number. all number supplied to this function should be integers, it returns 
     * the result as integer aswell. what ever value returned is the percentage equivalent. Take for example we have 500 as the main number and we 
     * also have another number which is 100, if we want to know the percentage of 100 in 500, then we shall be dividing 100 with 500 and multiplying 
     * the resultant number with 100 which gives us 20, meaning the percentage of 100 in 500 is 20%, therefore so in this function, 500 will go into 
     * the "$Parent_Number" parameter, while 100 will go into the "$Percentage_Value_number" parameter, hence the function would return 20. */
function Number_To_Percentage_Equivalent($Parent_Number, $Percentage_Value_number)
{
    /* perform operation smoothly and quickly here.   */
    return (($Percentage_Value_number / $Parent_Number) * 100);
}





// FUNCTION TO HELP COMPARE BETWEEN TWO DATES. REGARDLESS OF WETHER THE DATES ARE OF THESAME FORMAT OR NOT THIS FUNCTION FIRST CONVERTS THE DATE
// TO ITS UNIX TIMESTAMP EQUAVALENT AND THEN USE THAT OUTPUT TO COMPARE AND CONTRAST.
// => IF "$OPERATOR" PARAMETER IS SETTED TO "EQUALS", FUNCTION CHECK IF BOTH "$FIRST_DATE AND $SECOND_DATE" ARE EQUAL OR NOT. 
// => IF "$OPERATOR" PARAMETER IS SETTED TO "GREATER_THAN", FUNCTION CHECK IF "$FIRST_DATE" IS GREATER THAN "$SECOND_DATE". 
// => IF "$OPERATOR" PARAMETER IS SETTED TO "GREATER_THAN_OR_EQUALS", FUNCTION CHECK IF "$FIRST_DATE" IS GREATER THAN OR EQUALS TO "$SECOND_DATE". 
// => IF "$OPERATOR" PARAMETER IS SETTED TO "LESS_THAN", FUNCTION CHECK IF "$FIRST_DATE" IS LESS THAN "$SECOND_DATE".
// => IF "$OPERATOR" PARAMETER IS SETTED TO "LESS_THAN_OR_EQUALS", FUNCTION CHECK IF "$FIRST_DATE" IS LESS THAN OR EQUALS TO "$SECOND_DATE".
function DATE_COMPARATOR($FIRST_DATE, $SECOND_DATE, $OPERATOR = "EQUALS")
{
    // Convert both dates to time, to ease out comparison.
    $REAL_FIRST_DATE = strtotime($FIRST_DATE);
    $REAL_SECOND_DATE = strtotime($SECOND_DATE);
    // Check operator type, and use the data to decide how this function should operate
    if ($OPERATOR === "EQUALS") {
        // Compare dates and return output
        if ($REAL_FIRST_DATE == $REAL_SECOND_DATE) {
            return true;
        } else {
            return false;
        }
    } else if ($OPERATOR === "GREATER_THAN") {
        // Compare dates and return output
        if ($REAL_FIRST_DATE > $REAL_SECOND_DATE) {
            return true;
        } else {
            return false;
        }
    } else if ($OPERATOR === "GREATER_THAN_OR_EQUALS") {
        // Compare dates and return output
        if ($REAL_FIRST_DATE >= $REAL_SECOND_DATE) {
            return true;
        } else {
            return false;
        }
    } else if ($OPERATOR === "LESS_THAN") {
        // Compare dates and return output
        if ($REAL_FIRST_DATE < $REAL_SECOND_DATE) {
            return true;
        } else {
            return false;
        }
    } else if ($OPERATOR === "LESS_THAN_OR_EQUALS") {
        // Compare dates and return output
        if ($REAL_FIRST_DATE <= $REAL_SECOND_DATE) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}















// HERE WE HAVE A FUNCTION THAT WILL HELP US CALCULATE THE DIFFERENCE BETWEEN TWO DATES, IN WHICH THE OUTPUTE OF THIS FUNCTION DEPENDS ON THE
// DATA INSIDE THE "OPERATOR" PARAMETER. PLEASE NOTE THAT IN A BID TO FIND THE DIFFERENCE BETWEEN TWO DATES, THIS FUNCTION SUBTRACTS THE FIRST
// DATE AWAY FROM THE SECOND DATE.

// USE THE "$FIRST_DATE" PARAMETER, TO ENTER THE FIRST DATE ('Y-m-d H:i:s').
// USE THE "$SECOND_DATE" PARAMETER, TO ENTER THE SECOND DATE ('Y-m-d H:i:s'). NOTE THAT IN A BID TO FIND THE DIFFERENCE BETWEEN TWO DATES, 
// THIS FUNCTION SUBTRACTS THE FIRST DATE AWAY FROM THE SECOND DATE ((SECOND_DATE - FIRST_DATE)).
// USE THE "$OPERATOR" PARAMETER, TO TELL THIS FUNCTION THE FORMAT THE DIFFERENTIATED RESULT SHOULD LOOK LIKE. THIS PARAMETER COLLECTS A STRING
// THAT CAN BE "YEAR" OR "MONTH" OR "DAY" OR "HOUR" OR "MINUTE" OR "SECOND" OR BETTER STILL, IT CAN BE ALL TOGETHER JUST LIKE THIS
// "YEAR MONTH DAY HOUR MINUTE SECOND" OR BETTER STILL IT CAN BE "YEAR MONTH DAY" OR "DAY HOUR" ETC. MANIPULATE THIS STRING THE WAY YOU WANT
// DEPENDING ON THE KIND OF OUTPUT YOU WANT. 
function GLOBAL_DATE_DIFFERENTIATOR($FIRST_DATE, $SECOND_DATE, $OPERATOR = "YEAR MONTH DAY HOUR MINUTE SECOND")
{
    // array to store result
    $Result = array();
    // Here we define the two dates and we Convert both dates to time, to ease out date-differentiation.
    $REAL_FIRST_DATE = strtotime($FIRST_DATE);
    $REAL_SECOND_DATE = strtotime($SECOND_DATE);
    // Formulate the Difference between two dates
    $DIFFERENCE = abs($REAL_SECOND_DATE - $REAL_FIRST_DATE);
    // declare base variables
    $years = $months = $days = $hours = $minutes = "";

    // Check operator type string to see if it has the "YEAR" signature in it, if it does we run this function
    if (Text_in_String_SEARCHER("YEAR", $OPERATOR) !== false) {
        // run calculation
        $cal = floor($DIFFERENCE / (365 * 60 * 60 * 24));
        // To get the year divide the resultant date into, total seconds in a year (365*60*60*24)
        $Result[] = $years = ($cal > 1) ? $cal . " Years" : $cal . " Year";
    }

    // Check operator type string to see if it has the "MONTH" signature in it, if it does we run this function
    if (Text_in_String_SEARCHER("MONTH", $OPERATOR) !== false) {
        // run calculation
        $cal = floor(($DIFFERENCE - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
        // To get the month, subtract it with years and divide the resultant date into total seconds in a month (30*60*60*24)
        $Result[] = $months = ($cal > 1) ? $cal . " Months" : $cal . " Month";
    }

    // Check operator type string to see if it has the "DAY" signature in it, if it does we run this function
    if (Text_in_String_SEARCHER("DAY", $OPERATOR) !== false) {
        // run calculation
        $cal = floor(($DIFFERENCE - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));
        // To get the day, subtract it with years and months and divide the resultant date into total seconds in a days (60*60*24)
        $Result[] = $days = ($cal > 1) ? $cal . " Days" : $cal . " Day";
    }

    // Check operator type string to see if it has the "HOUR" signature in it, if it does we run this function
    if (Text_in_String_SEARCHER("HOUR", $OPERATOR) !== false) {
        // run calculation
        $cal = floor(($DIFFERENCE - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24) / (60 * 60));
        // To get the hour, subtract it with years, months & seconds and divide the resultant date into total seconds in a hours (60*60)
        $Result[] = $hours = ($cal > 1) ? $cal . " Hours" : $cal . " Hour";
    }

    // Check operator type string to see if it has the "MINUTE" signature in it, if it does we run this function
    if (Text_in_String_SEARCHER("MINUTE", $OPERATOR) !== false) {
        // run calculation
        $cal = floor(($DIFFERENCE - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24 - $hours * 60 * 60) / 60);
        // To get the minutes, subtract it with years, months, seconds and hours and divide the resultant date into total seconds i.e. 60
        $Result[] = $minutes = ($cal > 1) ? $cal . " Minutes" : $cal . " Minute";
    }

    // Check operator type string to see if it has the "SECOND" signature in it, if it does we run this function
    if (Text_in_String_SEARCHER("SECOND", $OPERATOR) !== false) {
        // run calculation
        $cal = floor(($DIFFERENCE - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24 - $hours * 60 * 60 - $minutes * 60));
        // To get the minutes, subtract it with years, months, seconds, hours and minutes
        $Result[] = ($cal > 1) ? $cal . " Seconds" : $cal . " Second";
    }

    // Compile final outputes as a single string and return it.
    return implode(", ", $Result);
}







// A FUNCTION TO TAKE IN A FULL NAME OR PHRASE, AND EXTRACT THE STARTING LETTERS INTO AN ABBREVIATION. I.E JOHN DOE => J D
// USE THE "$STOP_POINT" PARAMETER TO SET THE MAXIMUM NUMBER OF LETTERS TO BE ALLOWED, FOR EXAMPLE IF WE HAVE A PHRASE SUCE AS 
// "JOHNSON MARK LAWRENCE PETERSIDE" AND THE "$STOP_POINT" PARAMETER IS SETTED TO 3, THE OUTPUTE WILL BE J M L.
function NAME_ABBREVIATOR($INPUTE, $STOP_POINT = 2)
{
    // CREATE A VARIABLE TO STORE FINAL RESULT
    $ABBREVIATION = "";
    // PROCESS THE DATA, TO SPLIT IT INTO SECTIONS
    $NAMES = explode(" ", $INPUTE);
    // CREATE A LOOP TO HOVER THROUGH THE NAMES AND EXTRACT THE FIRST LETTERS BEFORE CONCATING THEM.
    for ($i = 0; $i < count($NAMES); $i++) {
        // EXTRACT THE FIRST LETTERS AND CONCATE TO THE MAIN STRING
        $ABBREVIATION .= substr($NAMES[$i], 0, 1) . " ";
        // DEPENDING ON THE SETTED CHARACTER LENGHT OF THE ABBREVIATION NEEDED, WE RUN THIS TEST TO KNOW WHEN TO TERMINATE THIS LOOP
        if ($i === ($STOP_POINT - 1)) break;
    }
    // RETURN THE FINAL OUTPUT, BUT TRIM FIRST TO REMOVE EXTRA SPACE AT THE END OF THE FORMED STRING.
    return trim($ABBREVIATION);
}







/* make Avatar */
if (!function_exists('makeAvatar')) {
    function makeAvatar($fontPath, $dest, $char)
    {
        $path = $dest;
        $image = imagecreate(200, 200);
        $red = rand(0, 255);
        $green = rand(0, 255);
        $blue = rand(0, 255);
        imagecolorallocate($image, $red, $green, $blue);
        $textcolor = imagecolorallocate($image, 255, 255, 255);
        imagettftext($image, 100, 0, 50, 150, $textcolor, $fontPath, $char);
        imagepng($image, $path);
        imagedestroy($image);
        return $path;
    }
}









/**
 * Get the star rating for a given rating value.
 *
 * @param float $rating The rating value, between 1 and 5.
 * @return string The star rating, represented by Unicode stars.
 */
function getStarRating($rating, $starClass = '')
{
    // Return a default string if the rating is 0.
    if ($rating == 0) {
        return '<i class="' . $starClass . ' far fa-star"></i><i class="' . $starClass . ' far fa-star"></i><i class="' . $starClass . ' far fa-star"></i><i class="' . $starClass . ' far fa-star"></i><i class="' . $starClass . ' far fa-star"></i>';
    }

    // Calculate the number of full stars.
    $fullStars = floor($rating);

    // Determine whether to show a half star or a quarter star.
    $decimalPart = $rating - $fullStars;
    $hasHalfStar = $decimalPart >= 0.5;
    $hasQuarterStar = $decimalPart >= 0.25 && $decimalPart < 0.5;

    // Build the star rating string.
    $stars = '';
    for ($i = 0; $i < $fullStars; $i++) {
        $stars .= '<i class="' . $starClass . ' fas fa-star"></i>';
    }
    if ($hasHalfStar) {
        $stars .= '<i class="' . $starClass . ' fas fa-star-half-alt"></i>';
    }
    if ($hasQuarterStar) {
        $stars .= '<i class="' . $starClass . ' fas fa-star-quarter-alt"></i>';
    }
    for ($i = 0; $i < 5 - $fullStars - ($hasHalfStar ? 1 : 0) - ($hasQuarterStar ? 1 : 0); $i++) {
        $stars .= '<i class="' . $starClass . ' far fa-star"></i>';
    }

    // Return the star rating string.
    return $stars;
}




function timeElapsedString($datetime, $full = false)
{
    $now = Carbon::now();
    $ago = Carbon::parse($datetime);

    $diff = $now->diff($ago);

    if ($diff->y > 0) {
        $string = $diff->y . ' year';
        if ($diff->y != 1) {
            $string .= 's';
        }
    } elseif ($diff->m > 0) {
        $string = $diff->m . ' month';
        if ($diff->m != 1) {
            $string .= 's';
        }
    } elseif ($diff->d > 0) {
        $string = $diff->d . ' day';
        if ($diff->d != 1) {
            $string .= 's';
        }
    } elseif ($diff->h > 0) {
        $string = $diff->h . ' hour';
        if ($diff->h != 1) {
            $string .= 's';
        }
    } else {
        $string = 'less than an hour';
    }

    if (!$full) {
        $string = str_replace(['hours', 'hour'], 'h', $string);
        $string = str_replace(['days', 'day'], 'd', $string);
        $string = str_replace(['months', 'month'], 'mon', $string);
        $string = str_replace(['years', 'year'], 'y', $string);
    }

    return $string . ' ago';
}


//FUNCTION TO CHECK IF LINK PROVIDED IS A YOUTUBE LINK
function embedYouTubeVideo($videoLink)
{
    // Check if the provided link is a valid YouTube link, including shorts
    if (preg_match('/youtube\.com\/watch\?v=([a-zA-Z0-9_\-]+)/', $videoLink, $matches)) {
        $videoID = $matches[1];
        // Construct the embedded video link
        return "https://www.youtube.com/embed/$videoID";
    } elseif (preg_match('/youtu\.be\/([a-zA-Z0-9_\-]+)/', $videoLink, $matches)) {
        $videoID = $matches[1];
        // Construct the embedded video link for youtu.be links
        return "https://www.youtube.com/embed/$videoID";
    } elseif (preg_match('/youtube\.com\/shorts\/([a-zA-Z0-9_\-?]+)/', $videoLink, $matches)) {
        $videoID = $matches[1];
        // Construct the embedded video link for shorts
        return "https://www.youtube.com/embed/$videoID";
    } else {
        // If the provided link is not a valid YouTube link, return the original link
        return $videoLink;
    }
}


//FUNCTION TO CHECK IF LINK PROVIDED IS A VIMEO LINK
function embedVimeoVideo($videoLink)
{
    // Check if the provided link is a valid Vimeo link
    if (preg_match('/vimeo\.com\/(\d+)/', $videoLink, $matches)) {
        $videoID = $matches[1];
        // You can customize this hash if needed
        $hash = uniqid();
        // Construct the embedded video link
        return "https://player.vimeo.com/video/$videoID?h=$hash";
    } else {
        // If the provided link is not a valid Vimeo link, return the original link
        return $videoLink;
    }
}

function embedVideo($videoLink)
{
    if (strpos($videoLink, 'youtube.com') !== false || strpos($videoLink, 'youtu.be') !== false) {
        // It's a YouTube video link
        return embedYouTubeVideo($videoLink);
    } elseif (strpos($videoLink, 'vimeo.com') !== false) {
        // It's a Vimeo video link
        return embedVimeoVideo($videoLink);
    } else {
        // Not a recognized video link, return the original link
        return $videoLink;
    }
}
