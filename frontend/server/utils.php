<?php

function FormatTime($timestamp, $type = "FB")
{
    if(!is_numeric($timestamp)){
        $timestamp = strtotime($timestamp);
    }

    // Get time difference and setup arrays
    $difference = time() - $timestamp;
    $periods = array("segundo", "minuto", "hora", "dia", "semana", "mes", "years");
    $lengths = array("60","60","24","7","4.35","12");

    // Past or present
    if ($difference >= 0)
    {
        $ending = "Hace";
    }
    else
    {
        $difference = -$difference;
        $ending = "Dentro de";
    }

    // Figure out difference by looping while less than array length
    // and difference is larger than lengths.
    $arr_len = count($lengths);
    for($j = 0; $j < $arr_len && $difference >= $lengths[$j]; $j++)
    {
        $difference /= $lengths[$j];
    }

    // Round up
    $difference = round($difference);

    // Make plural if needed
    if($difference != 1)
    {
        $periods[$j].= "s";
    }

    // Default format
    $text = "$ending $difference $periods[$j]";

    // over 24 hours
    if($j > 2)
    {
        // future date over a day formate with year
        if($ending == "to go")
        {
            if($j == 3 && $difference == 1)
            {
                $text = "Ayer a las ". date("g:i a", $timestamp);
            }
            else
            {
                $text = date("F j, Y \a \l\a\s g:i a", $timestamp);
            }
            return $text;
        }

        if($j == 3 && $difference == 1) // Yesterday
        {
            $text = "Ayer a las ". date("g:i a", $timestamp);
        }
        else if($j == 3) // Less than a week display -- Monday at 5:28pm
        {
            $text = date(" \a \l\a\s g:i a", $timestamp);

            switch(date("l", $timestamp)){
                case "Monday":      $text = "Lunes" . $text; break;
                case "Tuesday":     $text = "Martes" . $text; break;
                case "Wednesday":   $text = "Miercoles" . $text; break;
                case "Thursday":    $text = "Jueves" . $text; break;
                case "Friday":      $text = "Viernes" . $text; break;
                case "Saturday":    $text = "Sabado" . $text; break;
                case "Sunday":      $text = "Domingo" . $text; break;
            }
        }
        else if($j < 6 && !($j == 5 && $difference == 12)) // Less than a year display -- June 25 at 5:23am
        {
            $text = date(" j \a \l\a\s g:i a", $timestamp);

            switch(date("F", $timestamp)){
                case "January":     $text = "Enero"     . $text; break;
                case "February":    $text = "Febrero"   . $text; break;
                case "March":       $text = "Marzo"     . $text; break;
                case "April":       $text = "Abril"     . $text; break;
                case "May":         $text = "Mayo"  . $text; break;
                case "June":        $text = "Junio"     . $text; break;
                case "July":        $text = "Julio"     . $text; break;
                case "August":      $text = "Agosto"    . $text; break;
                case "September":       $text = "Septiembre"    . $text; break;
                case "October":         $text = "Octubre"   . $text; break;
                case "November":        $text = "Noviembre"     . $text; break;
                case "December":        $text = "Diciembre"     . $text; break;
            }
        }
        else // if over a year or the same month one year ago -- June 30, 2010 at 5:34pm
        {
            $text = date(" j, Y \a \l\a\s g:i a", $timestamp);

            switch(date("F", $timestamp)){
                case "January":     $text = "Enero"     . $text; break;
                case "February":    $text = "Febrero"   . $text; break;
                case "March":       $text = "Marzo"     . $text; break;
                case "April":       $text = "Abril"     . $text; break;
                case "May":         $text = "Mayo"  . $text; break;
                case "June":        $text = "Junio"     . $text; break;
                case "July":        $text = "Julio"     . $text; break;
                case "August":      $text = "Agosto"    . $text; break;
                case "September":       $text = "Septiembre"    . $text; break;
                case "October":         $text = "Octubre"   . $text; break;
                case "November":        $text = "Noviembre"     . $text; break;
                case "December":        $text = "Diciembre"     . $text; break;
            }
        }
    }

    $text = "<span title='".date("F j, Y \a \l\a\s g:i a", $timestamp)."'> " . $text . "</span>";

    return $text;
}

