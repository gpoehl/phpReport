<?php

declare(strict_types=1);
/*
 * This file is part of the gpoehl/phpReport library.
 *
 * @license   GNU LGPL v3.0 - For details have a look at the LICENSE file
 * @copyright ©2019 Günter Pöhl
 * @link      https://github.com/gpoehl/phpReport/readme
 * @author    Günter Pöhl  <phpReport@gmx.net>
 */

namespace gpoehl\phpReport;

use InvalidArgumentException;
use NumberFormatter;

/**
 * Prototype provides some basic output to help developing classes using phpReport.
 * All methods may be called as prototye methods instead of owner methods.
 */
class Prototype {

    private $rep;           // The report object. 
    private $nfo;           // Number formatter for ordinal numbers
    // Table format to print action data. Has one header row and one table row.
    // Table row usually has multiple contents starting at column 4. 
    private $table = <<<'TABLE1'
        <table border="0"><tr>
            <th style="background-color: %1$s; width: %2$dpx; text-align: center">%3$s</th>  
            <th style="background-color: %1$s; width: 150px;">%4$s</th>
            <th style="width: %5$dpx"></th>
            <th style="text-align: left">%6$s</th>
        </tr>
        <tr><td colspan="3"></td><td>%7$s<td></tr>
        </table>
TABLE1;
    // Single information table. Multiple content tables are concatenated and
    // pinted in table row of $table.
    private $contentTable = <<<'TABLE2'
          <table border="0"><tr><td style="vertical-align: top; width: 150px"><strong>%1$s </strong></td><td>%2$s</td></tr></table>     
    TABLE2;
    // Background colors for action keys. Columns 1 and 2 of table header in $table.
    private $colors = [
        'init' => '#ccff33',
        'close' => '#ccff33',
        'totalHeader' => '#b4faa3',
        'totalFooter' => '#b4faa3',
        'groupHeader' => '#93db00',
        'groupFooter' => '#93db00',
        'detail' => '#ffb76f',
        'noData' => '#ff4000',
        'noData_n' => '#f78181',
        'noGroupChange_n' => '#f88080',
        'data_n' => '#ff8000',
    ];
    // Additional option signs for action keys 
    private $signs = [
        'groupHeader' => '&gt;',
        'groupFooter' => '&lt;',
        'detail' => '&#9826;',
        'noData' => '&#9826;',
        'noData_n' => '&#9826;',
    ];

    /**
     * @param report $report The php report object
     */
    public function __construct(report $report) {
        $this->rep = $report;
        $this->nfo = new NumberFormatter('EN_US', NumberFormatter::ORDINAL);
    }

    /**
     * Build a html table with data related to a method
     * Header line has a sign, method name and level with a fixed width.
     * Then the group name and optional headerData follows in column 4.
     * Lines after header line are empty in the first 3 colums. $content
     * is printed at column 4.
     * @param string $content Data prepared in methods
     * @param string $headerData Additional data to be printed in method header line.
     * @return string Html table with all data for a method
     */
    private function renderAction(string $content, string $headerData = ''): string {
        $methodKey = $this->rep->currentAction->actionKey;
        $headerCol3 = $this->getMethodName() . $headerData;
        $sign = ($this->signs[$methodKey]) ?? '';
        $color = substr($this->colors[$methodKey], 1);
        // Modify groupheader and footer background colors. Using hex color codes
        // of a method and increase this by hex(30) * actual Level
        if (substr($methodKey, 0, 5) === 'group') {
            $color = '#' . dechex(hexdec($color) + (hexdec("000030") * $this->rep->getLevel()));
        }
        // width of column 1
        $width = ($this->rep->getLevel() > 0) ? $this->rep->getLevel() * 16 + 10 : 0;
        // width of column 3 (constant is sum of column 1 and column 3
        $widthCol3 = 100 - $width;

        if (!in_array($methodKey, ['init', 'close', 'noData'])) {
            $methodKey .= '&nbsp;' . $this->rep->getLevel();
        }
        return "\n" . sprintf($this->table, $color, $width, $sign, $methodKey, $widthCol3, $headerCol3, $content);
    }

    /**
     * Call related method to the one last called in target class.
     * Parameters are pulled here to simplify the report class. 
     * @return string Html table generated by method to be called
     */
    public function magic(): string {
        $method = $this->rep->currentAction->actionKey;
        switch ($method) {
            case 'groupHeader':
            case 'groupFooter':
                return $this->$method(
                        $this->rep->getGroupValue(),
                        $this->rep->getRow(),
                        $this->rep->getRowKey(),
                        $this->rep->getDimID(),
                );
            case 'detail':
            case 'noData_n':
            case 'data_n':
            case 'noGroupChange_n':
                return $this->$method(
                        $this->rep->getRow(),
                        $this->rep->getRowKey(),
                        $this->rep->getDimID(),
                );
            default:
                return $this->$method();
        }
    }

    /**
     * Get the name of method which would have been called in target class
     * 
     * @return string When action is a string or a closure the related constant
     * is returned.
     */
    private function getMethodName(): string {
        $action = $this->rep->currentAction;
        switch ($action->givenActionTyp) {
            // For typ method return only the method name (second array element)
            case Action::METHOD:
                return $action->givenAction;
            case Action::CALLABLE:
                // Build method name from class and method
                $name = '';
                is_callable($action->givenAction, true, $name);
                // cast because $name for an anonymous class might include substring \000 which represents octal notation 
                return (string) $name;
            case Action::CLOSURE:
                return 'closure';
            case Action::STRING:
                return 'string: ' . htmlentities(substr($action->givenAction, 0, 60));
            default:
                // WARNING and ERROR are handled in Report class
                throw InvalidArgumentException("Call type '$action->givenActionTyp' is invalid");
        }
    }

    public function init(): string {
        return $this->renderAction('Place here all stuff to initialize the job.');
    }

    public function close(): string {
        return $this->renderAction('Cleanup your dishes here.');
    }

    public function totalHeader(): string {
        return $this->renderAction('A good place to print selection criteria or a cover page.');
    }

    public function totalFooter(): string {
        $content = 'A good place to print global summaries.';
        $content .= $this->renderTotals();
        $content .= $this->renderRowCounter();
        $content .= $this->renderChildGroupCounter();
        return $this->renderAction($content);
    }

    public function groupHeader($val, $row, $rowKey, $dimID): string {
        $content = $this->renderRowValues($row, $rowKey);
        $content .= $this->renderGroupCounter();
        $val = $val ?? 'Null';
        return $this->renderAction($content, ", Dim = $dimID, Group value = $val");
    }

    /**
     * Prepare output for group footers
     * Note that $this->rep->dims->current has the dim which forced the group change.
     * The related dim to a group must be get out of the group object.
     * @param type $val
     * @param type $row
     * @param type $rowKey
     * @return string Table with data related to a group footer
     */
    public function groupFooter($val, $row, $rowKey, $dimID): string {
        $content = ($this->rep->isLast()) ?
                "I'm the last " . $this->rep->getGroupName() :
                "There are more {$this->rep->getGroupName()}(s)";
        $content .= ($this->rep->getLevel() === 1) ? ' within this job.' :
                ' in group ' . $this->rep->getGroupName($this->rep->getLevel() - 1);
        $content .= '<br>' . $this->renderRowValues($row, $rowKey);
        $content .= $this->renderTotals();
        $content .= $this->renderSheets($this->rep->getLevel());

        $content .= $this->renderRowCounter();
        $content .= $this->renderGroupCounter();
        $content .= $this->renderChildGroupCounter();
        $val ??= 'Null';
        return $this->renderAction($content, ", Dim = $dimID, Group value = $val");
    }

    public function detail($row, $rowKey): string {
        $content = $this->renderRowValues($row, $rowKey);
        $content .= $this->renderTotals();
        $content .= $this->renderRowCounter();
        return $this->renderAction($content, ", Dim = {$this->rep->getDimID()} RowKey = $rowKey");
    }

    public function noData(): string {
        return $this->renderAction('No data passed to this job.');
    }

    /**
     * Create output when no data was given in $dimID for next dimension
     * @param int $dimID The dimension id of row not having data for next dimension. 
     * @return string Created output 
     */
    public function noData_n($row, $rowKey, int $dimID): string {
        $missingDimID = $dimID + 1;
        $content = '' //" Value of higher group level $groupName = $val."
                . "<br>Row values belongs to dimension $dimID!";
        $content .= $this->renderRowValues($row, $rowKey);
        return $this->renderAction($content, ", Dimension $dimID not providing data for dimension $missingDimID");
    }

    /**
     * Create output for row of dimensions but not for the last dimenion.
     * This is similar to the detail action for the last dimension.
     * @param type $row
     * @param type $rowKey
     * @param int $dimID The current dimension id
     * @return string Created output 
     */
    public function data_n($row, $rowKey, int $dimID): string {
        $content = $this->renderRowValues($row, $rowKey);
        $content .= $this->renderRowCounter();
        return $this->renderAction($content, ", Dim = $dimID Rowkey = $rowKey");
    }

    /**
     * Create output when row in dimensions didn't trigger a group change.
     * @param type $row
     * @param type $rowKey
     * @param int $dimID The current dimension id
     * @return string Created output 
     */
    public function noGroupChange_n($row, $rowKey, int $dimID): string {
        $content = $this->renderRowValues($row, $rowKey);
        return $this->renderAction($content, ", Dim = $dimID");
    }

    /**
     * Prepare values of $row to be printed in one line
     * @param type $row
     * @param type $rowKey
     * @return string Values within $row 
     */
    private function renderRowValues($row, $rowKey): string {
        if ($row === null) {
            $out = 'No data given.';
        } else {
            $out = json_encode($row);
            // Truncate to a maximum length of 250
            if (strlen($out) > 250) {
                $out = substr($out, 0, 245) . ' ...';
            }
        }
        return "\n" . sprintf($this->contentTable, 'Row values:', $out);
    }

    /**
     * Render totals for the current level
     * @return string html table of values for attributes defined by sum() or sheet() methods.
     */
    private function renderTotals(): string {
        if (empty($this->rep->total->items)) {
            return "\n" . sprintf($this->contentTable, 'Aggregated attributes:', 'Nothing to be summarized.');
        }
        $out = "<table border='1'" . ' style="border-collapse: collapse">'
                . "\n<tr><th>Name</th><th>sum</th><th>nn</th><th>nz</th><th>min</th><th>max</th></tr>";
        foreach ($this->rep->total->items as $name => $attr) {
            $out .= '<tr><td>' . $name . '</td>';
            $out .= '<td>' . $attr->sum() . '</td>';
            if (is_a($attr, 'NnAndNzCounterIF')) {
                $out .= '<td>' . $attr->nn() . '</td>';
                $out .= '<td>' . $attr->nz() . '</td>';
            } else {
                $out .= '<td>-</td><td>-</td>';
            }
            if (is_a($attr, 'MinMaxIF')) {
                $out .= '<td>' . $attr->min() . '</td>';
                $out .= '<td>' . $attr->max() . '</td></tr>';
            } else {
                $out .= '<td>-</td><td>-</td>';
            }
        }
        return "\n" . sprintf($this->contentTable, 'Aggregated attributes:', $out . '</table>');
    }

    /**
     * Not yet implemented!!!!!!!!!!!!!
     * Render sheets on current level
     * Totals has names of aggregated attributes, sum, nn and nz
     * @param int $level
     * @return string html table of total values
     */
    private function renderSheets(int $level): string {
        if (!isset($this->rep->total->sheets)) {
            return '';
        }
        $out = '';
//        foreach (array_keys($this->rep->buckets[$level]) as $calcName) {
//            $name[] = $calcName;
//            foreach ($calcname as $bucket) {
//                $sum[] = $this->rep->sum($calcName, $level);
//                $nn[] = $this->rep->nnCount($calcName, $level);
//                $nz[] = $this->rep->nzCount($calcName, $level);
//            }
//        }
//        $out = "\n<tr><th>Name</th><th>" . implode('</th><th>', $name) . '</th></tr>';
//        $out .= "\n<tr><td>sum</td><td>" . implode('</td><td>', $sum) . '</td></tr>';
//        $out .= "\n<tr><td>nn</td><td>" . implode('</td><td>', $nn) . '</td></tr>';
//        $out .= "\n<tr><td>nz</td><td>" . implode('</td><td>', $nz) . '</td></tr>';
//        $out = "<table border='1'" . ' style="border-collapse: collapse">' . $out . "</table>";
        return "\n" . sprintf($this->contentTable, 'Prototyp of sheet is not yet implemented:', $out);
    }

    /**
     * Render row counter
     * Renders a table for all row counters from level 0 to the actual level
     * @return string html table of row counters
     */
    private function renderRowCounter(): string {
        $maxLevel = end($this->rep->rc->items)->maxLevel;
        $out = '<table border="1" style="border-collapse: collapse"><tr><th>Level</th>';
        for ($i = 0; $i <= $maxLevel; $i++) {
            $out .= "<th>$i</th>";
        }
        $out .= '</tr>';

        foreach ($this->rep->rc->items as $dim => $rc) {
            $out .= "<tr><td>Dim $dim</td>";
            for ($i = 0; $i <= $maxLevel; $i++) {
                if ($i <= $rc->maxLevel) {
                    $out .= '<td>' . $rc->sum($i) . '</td>';
                } else {
                    $out .= '<td></td>';
                }
            }
            $out .= '</tr>';
        }
        return "\n" . sprintf($this->contentTable, 'Row counter:', $out . '</table>');
    }

    /**
     * Render group counter
     * Numbers shown are for the group counter of the current level.
     *  
     * Renders a table for the group counter from the currnet level - 0 up to level 0. 
     * It could also be described as "The current group is the nth occurrece 
     * within a higer group.
     * @return string html table of row counters
     */
    private function renderGroupCounter(): string {
        $level = $this->rep->getLevel();
        $out = "Group $level is ";
        // Walk up from current level - 1 to level = 0  
        for ($i = $level - 1; $i >= 0; $i--) {
            $out .= $this->nfo->format($this->rep->gc->items[$level]->sum($i)) . " time in group $i, ";
        }
        return "\n" . sprintf($this->contentTable, 'Group counter &uarr;:', substr($out, 0, -2) . '.');
    }

    /**
     * Render group counter for child groups
     * Child groups are those on lower level of a given level. 
     * @return string html table of row counters
     */
    private function renderChildGroupCounter(): string {
        $level = $this->rep->getLevel();
        $maxLevel = array_key_last($this->rep->getGroupNames());
        if ($level >= $maxLevel) {
            $out = "Group $level is the lowest group level. So there are no child groups  ";
        } else {
            $out = "Group $level has ";
            $numberOfGroups = $maxLevel;
            $wrk = $this->rep->gc->range([$level + 1])->sum($level, true);
            foreach ($wrk as $key => $sum) {
                $out .= "$sum times group $key, ";
            }
        }
        return "\n" . sprintf($this->contentTable, 'Group counter &darr;:', substr($out, 0, -2) . '.');
    }

}
