<?php

    function processTimeString(string $hoursUnprocessed) : float
    {
        $endHours = strpos($hoursUnprocessed, "h");
        $hours = "";
        $minutes = "";
        if ($endHours)
            $hours = substr($hoursUnprocessed, 0, $endHours);
        $endMinutes = strpos($hoursUnprocessed, "m");
        if ($endMinutes && $endHours)
            $minutes = substr($hoursUnprocessed, $endHours + 1, $endMinutes - $endHours - 1);
        else if ($endMinutes)
            $minutes = substr($hoursUnprocessed, 0, $endMinutes);
        $totalHours = 0.0;
        if ($hours)
            $totalHours += $hours;
        if ($minutes)
            $totalHours +=  $minutes / 60;
        return $totalHours;
    }
    /**
     * @param string $activitiesString - contains unprocessed information for all the activities of a worker
     * @return array $activitiesProcessed - array containing an array for each activity with the code, name,
     * hours worked, hourly rate and total money sum
     */
    function processActivities(string $activitiesString) : array
    {
        $activitiesUnprocessed = explode(",", $activitiesString);
        $activitiesProcessed = [];
        natcasesort($activitiesUnprocessed);
        foreach ($activitiesUnprocessed as $activity) {
            list($activityCode, $activityName, $hourAndRate) = explode(";", $activity);
            $activityCode = ltrim($activityCode, "[");
            $activityName = str_replace("_", " ", $activityName);
            list($hoursUnprocessed, $rateUnprocessed) = explode("*", $hourAndRate);
            $totalHours = processTimestring($hoursUnprocessed);
            $rate = substr($rateUnprocessed, 0, strpos($rateUnprocessed, "/"));
            $activityInfo = [$activityCode, $activityName, $totalHours, $rate, $totalHours * $rate];
            array_push($activitiesProcessed, $activityInfo);
        }
        return $activitiesProcessed;
    }

    /**
     * @param string $taxesString - string containing unprocessed information for all the taxes paid by a worker
     * @param float $totalBeforeTaxes - total of money earned by worker before subtracting taxes
     * @return array $taxesProcessed - array containing an array for each tax with the name and the percentage
     * the tax
     */
    function processTaxes(string $taxesString, float $totalBeforeTaxes) : array
    {
        $taxes = explode("%", $taxesString);
        $taxesProcessed = [];
        $matches = [];
        foreach ($taxes as $tax) {
            if ($tax == PHP_EOL) {
                continue;
            }
            preg_match("/(?<taxName>[a-zA-Z]+)(?<taxPercentage>\d+[\.,]\d+)$/", $tax, $matches);
//            preg_match("/\d/", $tax, $matches, PREG_OFFSET_CAPTURE);
//            $taxName = substr($tax, 0, $matches[0][1]);
//            $taxPercentage = str_replace(",", ".", substr($tax, $matches[0][1], strlen($tax) - $matches[0][1]));
            $matches["taxPercentage"] = str_replace(",", ".", $matches["taxPercentage"]);
            $taxValue = (float)$matches["taxPercentage"] * $totalBeforeTaxes / 100;
            array_push($taxesProcessed, [$matches["taxName"], $matches["taxPercentage"], $taxValue]);
//            $taxValue = $taxPercentage * $totalBeforeTaxes / 100;
//            array_push($taxesProcessed, [$taxName, $taxPercentage, $taxValue]);
        }
        return $taxesProcessed;
    }

    function formatActivitiesOutput(array $activitiesProcessed) : string
    {
        $output = sprintf(
            '%s|%s|%s|%s|%s',
            str_pad("Cod activitate",17),
            str_pad("Nume activitate", 17, " " , STR_PAD_LEFT),
            str_pad("Ore", 7, " ", STR_PAD_LEFT),
            str_pad("Rata orara", 10, " ", STR_PAD_LEFT),
            str_pad("Suma primita", 13, " ", STR_PAD_LEFT)
        );
        $output .= PHP_EOL;
        foreach ($activitiesProcessed as $activityInfo) {
            $col1 = str_pad($activityInfo[0],17);
            $col2 = str_pad($activityInfo[1], 17, " ", STR_PAD_LEFT);
            $col3 = str_pad($activityInfo[2], 7, " ", STR_PAD_LEFT);
            $col4 = str_pad(money_format('%.1i', $activityInfo[3]), 10, " ", STR_PAD_LEFT);
            $col5 = str_pad(money_format('%.2i', $activityInfo[4]), 13, " ", STR_PAD_LEFT);
            $output .= $col1."|".$col2."|".$col3."|".$col4."|".$col5.PHP_EOL;
        }
        return $output;
    }

    function formatTaxesOutput(array $taxesProcessed) : string
    {
        $output = "Contributii".PHP_EOL;
        foreach ($taxesProcessed as $tax) {
            $col1 = str_pad(mb_strtoupper($tax[0]),43)." ";
            $col2 = str_pad($tax[1]."%",10, " ", STR_PAD_LEFT)."|";
            $col3 = str_pad(money_format("%i", $tax[2]), 13, " ", STR_PAD_LEFT).PHP_EOL;
            $output .= $col1.$col2.$col3;
        }
        return $output;
    }

    function formatFirstName(string $firstName) : string
    {
        return ucwords($firstName);
    }

    function formatLastName(string $lastName) : string
    {
        return mb_convert_case($lastName, MB_CASE_UPPER);
    }

    function getOutputFullName($firstName, $lastName) : string
    {
        return str_pad("Nume", 17)."|".$lastName." ".$firstName.PHP_EOL;
    }

    function getOutputCNP(string $cnp) : string
    {
        return str_pad("CNP", 17)."|".$cnp.PHP_EOL;
    }

    function getOutputTotalBeforeTaxes(float $totalBeforeTaxes) : string
    {
        return str_pad("TOTAL BRUT",55).str_pad(money_format("%i", $totalBeforeTaxes),14, " ", STR_PAD_LEFT).PHP_EOL;
    }

    function getOutputTotalAfterTaxes(float $totalAfterTaxes) : string
    {
        $col1 = str_pad("TOTAL", 55);
        $col2 = str_pad(money_format("%i", $totalAfterTaxes), 13, " ", STR_PAD_LEFT);
        return $col1.$col2;
    }

    /**
     * @param string $firstName - contains the firstname of the worker
     * @param string $lastName - contains the lastnamae of the worker
     * @param string $cnp - contains the cnp of the worker
     * @param array $activitiesProcessed - contains all the activities of a worker with the name, code, hours worked,
     * hourly rate and total sum of money for each activity
     * @param array $taxesProcessed - contains all the taxes paid for a worker with the name and percentage for
     * each tax
     * @param  float $totalBeforeTaxes - salary before subtracting taxes
     * @param float $totalAfterTaxes - salary after subtracting taxes
     * @description: display the given information in a formatted way
     */
    function displaySalaryCard(string $firstName, string $lastName, string $cnp, array $activitiesProcessed, float $totalBeforeTaxes, array $taxesProcessed, float $totalAfterTaxes)
    {
        setlocale(LC_MONETARY, 'ro_RO.UTF-8');
        $firstName = formatFirstName($firstName);
        $lastName = formatLastName($lastName);
        //echo(extension_loaded("xdebug")?"yes":"no").PHP_EOL;
        $output = "<pre>";
        //echo(str_pad("",69,"^")).PHP_EOL;
        $output .= str_repeat("^", 69).PHP_EOL.PHP_EOL;
        $output .= getOutputFullName($firstName, $lastName);
        $output .= getOutputCNP($cnp);
        $output .= PHP_EOL;
        $output .= formatActivitiesOutput($activitiesProcessed);
        //echo(str_pad("",69,"-")).PHP_EOL;
        $output .= str_repeat("-", 69).PHP_EOL;
        $output .= getOutputTotalBeforeTaxes($totalBeforeTaxes);
        $output .= PHP_EOL;
        $output .= formatTaxesOutput($taxesProcessed);
        $output .= PHP_EOL;
        $output .= getOutputTotalAfterTaxes($totalAfterTaxes);
        $output .= "</pre>";
        echo($output);
    }

    function getTotalBeforeTaxes(array $activities) : float
    {
        $total = 0.0;
        foreach ($activities as $activity) {
            $total += $activity[4];
        }
        return $total;
    }

    function getTotalTaxesPaid(array $taxes, float $totalBrut) : float
    {
        $totalTaxes = 0.0;
        foreach ($taxes as $tax) {
            $totalTaxes += $tax[1] * $totalBrut / 100;
        }
        return $totalTaxes;
    }

    function getTotalAfterTaxes(float $beforeTaxes, float $totalTaxes) : float
    {
        return $beforeTaxes - $totalTaxes;
    }

    /**
     * @param string $input
     */
    function processSalaryString(string $input)
    {
        list($firstName, $lastName, $cnp, $activitiesString, $taxes) = explode("|", $input);
        $firstName = str_replace("+", " ", $firstName);
        $activitiesProcessed = processActivities($activitiesString);
        $totalBrut = getTotalBeforeTaxes($activitiesProcessed);
        $taxesProcessed = processTaxes($taxes, $totalBrut);
        $totalAfterTaxes = getTotalAfterTaxes($totalBrut, getTotalTaxesPaid($taxesProcessed, $totalBrut));
        displaySalaryCard($firstName, $lastName, $cnp, $activitiesProcessed, $totalBrut, $taxesProcessed, $totalAfterTaxes);
    }

    $handle = fopen("teste", "r");
    if (!$handle) {
        echo("Couldn't open file.").PHP_EOL;
        die;
    }

    while (($line = fgets($handle)) !== false) {
        //$line = readline("Insert line:");
        processSalaryString($line);
    }
    fclose($handle);