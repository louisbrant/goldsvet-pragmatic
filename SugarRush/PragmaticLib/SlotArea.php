<?php

namespace VanguardLTE\Games\SugarRush\PragmaticLib;

class SlotArea
{
    public static function getSlotArea($gameSettings, $reelset, $log)
    {
        $reelset = explode('~', $gameSettings['reel_set' . $reelset]);
        $reels = self::generateReels($reelset, $gameSettings);

        if ($log && ($log['State'] === 'Respin' || $log['State'] === 'FirstRespin')) {
            $reels = self::applyRespin($reels, $log, $gameSettings);
        }

        $slotArea = self::flattenReels($reels);

        return [
            'SlotArea' => $slotArea,
            'SymbolsAfter' => array_column($reels, 0),
            'SymbolsBelow' => array_column($reels, count($reels[0]) - 1),
        ];
    }

    private static function generateReels($reelset, $gameSettings)
    {
        $reels = [];

        foreach ($reelset as $key => $value) {
            if (is_array($value)) {
                // Cycle the reelset
                $reelsetCycled = array_merge($value, array_slice($value, 0, 10));
                $randomIndex = rand(0, count($reelsetCycled) - 1);
                $reels[$key] = array_slice($reelsetCycled, $randomIndex, $gameSettings['sh']);
            } else {
                // Handle the case where $value is not an array
                // You can log an error, provide a default array, or take appropriate action
                error_log('Error: $value is not an array. Key: ' . $key);
                // Example: Provide a default array
                $reels[$key] = array_fill(0, $gameSettings['sh'], 'default_symbol');
            }
        }

        return $reels;
    }

    private static function applyRespin($reels, $log, $gameSettings)
    {
        $currentSymbolsAfter = array_column($reels, count($reels[0]) - 1);
        $winPositions = array_merge(...array_column($log['WinLines'], 'Positions'));

        foreach ($reels as $key => &$reel) {
            // Check if the array key exists before accessing it
            if (isset($currentSymbolsAfter[$key])) {
                $reel = array_merge(
                    array_slice($reel, count($reel) - $gameSettings['sh']),
                    array_slice($currentSymbolsAfter, $key, 1),
                    $reel
                );
            } else {
                // Handle the case when the array key does not exist
                // You can log an error, provide a default value, or take appropriate action
                error_log('Error: Undefined array key ' . $key);
                // Example: Provide a default value
                $reel = array_merge(array_slice($reel, count($reel) - $gameSettings['sh']), ['default_symbol'], $reel);
            }

            $reel = array_values(array_diff_key($reel, array_flip($winPositions)));
        }

        return $reels;
    }

    private static function flattenReels($reels)
{
    $slotArea = [];

    foreach ($reels as $i => $reel) {
        foreach ($reel as $j => $symbol) {
            // Check if the array key exists before accessing it
            if (isset($reels[$j][$i])) {
                $slotArea[] = $reels[$j][$i];
            } else {
                // Handle the case when the array key does not exist
                // You can log an error, provide a default value, or take appropriate action
                error_log('Error: Undefined array key at position [' . $j . '][' . $i . ']');
                // Example: Provide a default value
                $slotArea[] = 'default_symbol';
            }
        }
    }

    return $slotArea;
}

}
