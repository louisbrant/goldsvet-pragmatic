<?php

namespace VanguardLTE\Games\SugarRush\PragmaticLib;

class WinChecker
{
    private $paytable;

    public function __construct($gameSettings)
    {
        $paytable = explode(';', $gameSettings['paytable']);
        $this->paytable = [];

        foreach ($paytable as $item) {
            $this->paytable[] = explode(',', $item);
        }
    }

    public function getWin($bet, &$slotArea)
{
    $winSymbols = [];
    $totalWin = 0;
    $isIncluded = array_fill_keys(array_keys($slotArea['SlotArea']), 0);
    $groups = [];

    self::getGroup($slotArea['SlotArea'], $isIncluded, $groups, 0, 0);

    $isMulti = mt_rand(0, 100);

    // Initialize $slm_mp here
    $slm_mv = [];
    $slm_mp = [];
    $slm_lmv = [];
    $slm_lmi = [];

    foreach ($groups as $group) {
        if ($isMulti < 40 && count($group['pos']) > 4) {
            foreach ($group['pos'] as $pos) {
                if (mt_rand(0, 100) < 40) {
                    $slm_mv[] = 2;
                    $slm_mp[] = $pos;
                    $group['multi'] *= 2;
                }
            }
            if ($group['multi'] > 1) {
                $slm_lmv[] = $group['multi'];
                $slm_lmi[] = count($slm_lmi);
            }
        }

        $symbolCount = count($this->paytable[$group['symbol']])0;
        $posCount = count($group['pos']);
        $paytableElement = $this->paytable[$group['symbol']][$symbolCount - $posCount] 0;
        $win = round($paytableElement * $bet * $group['multi'], 2);

        if ($win > 0) {
            var_dump('symbol=' . $group['symbol'] . ' pos=' . implode(',', $group['pos']));
            $winPositions = $group['pos'];
            $winSymbols[] = [
                'WinSymbol' => $group['symbol'],
                'CountSymbols' => count($group['pos']),
                'Pay' => $win,
                'Positions' => $winPositions
            ];
            $totalWin += $win;
        }
    }

    if (count($slm_mp)) {
        $slotArea['slm_mp'] = $slm_mp;
        $slotArea['slm_mv'] = $slm_mv;
        $slotArea['slm_lmv'] = $slm_lmv;
        $slotArea['slm_lmi'] = $slm_lmi;
    }

    return ['TotalWin' => $totalWin, 'WinLines' => $winSymbols];
}


    public static function getGroup($slotarea, &$isIncluded, &$group, $level, $pos)
    {
        if ($level == 0) {
            while ($pos < 49) {
                if (isset($isIncluded[$pos]) && !$isIncluded[$pos]) {
                    $isIncluded[$pos] = 1;
                    $group[] = [
                        'symbol' => $slotarea[$pos],
                        'pos' => [$pos],
                        'multi' => 1
                    ];
                    self::getGroup($slotarea, $isIncluded, $group, $level + 1, $pos);
                }
                $pos += 1;
            }
        } else {
            $groupId = count($group) - 1;
            $offsets = [1, 7, -1, -7];
            if ($pos % 7 == 0) {
                $offsets = [1, 7, -7];
            } elseif ($pos % 7 == 6) {
                $offsets = [7, -1, -7];
            }
            foreach ($offsets as $offset) {
                $newPos = $pos + $offset;

                // Check if $newPos is within valid range and meets the conditions
                if ($newPos >= 0 && $newPos < 49 && !$isIncluded[$newPos] && isset($slotarea[$newPos]) && $slotarea[$newPos] == $group[$groupId]['symbol']) {
                    $isIncluded[$newPos] = 1;
                    $group[$groupId]['pos'][] = $newPos;
                    self::getGroup($slotarea, $isIncluded, $group, $level + 1, $newPos);
                } else {
                    // Handle the case when the condition is not met
                    // You may want to log an error or take appropriate action
                    error_log("Error: Unexpected condition in WinChecker.php at line 101");
                    // Example: Provide a default value or take appropriate action
                    $isIncluded[$newPos] = 1; // Set a default value for $newPos
                }
            }
        }
    }
}
