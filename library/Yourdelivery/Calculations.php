<?php
/**
 * The one and ONLY class to do all the calculations needed for orders and stuff!
 *
 * @author Jan Oliver Oelerich
 */
class Yourdelivery_Calculations {

    /**
     * converts €,cc or € or €,c to €cc
     * @param string $price
     * @return int
     */
    public static function priceToInt($price) {
        $p = explode(',',$price);
        if(!isset($p[1])) 
            return ($p[0]*100);
        return ($p[0]*100+$p[1] < 0) ? 0 : $p[0]*100+$p[1];
    }

    /**
     * Create pageination string based on actual page, total pages coutn and link
     */    
    public static function constructPagination($page, $pages, $url) {
        $pageination = '';

        if($pages > 1) {
            if($pages > 9) {
                if ($page > 9) {
                    $pageination .= ' <a href="' . $url . '/page/' . ($page-10) . '">-10 |</a> ';
                }
                else {
                    $pageination .= ' -10 | ';
                }

                if ($page > 0) {
                    $pageination .= ' <a href="' . $url . '/page/' . ($page-1) . '">&lt;&lt;</a> ';
                }
                else {
                    $pageination .= ' &lt;&lt; ';
                }
            }

            if ($pages < 11) {
                for ($counter = 0 ; $counter < $pages ; $counter++) {
                    if ($counter>0) {
                        $pageination .= ' - ';
                    }

                    if ($counter == $page) {
                        $pageination .= '<b><font size="2">' . ($counter + 1) . '</font></b>';
                    }
                    else {
                        $pageination .= '<a href="' . $url . '/page/' . $counter . '">' . ($counter + 1) . '</a>';
                    }
                }
            }
            else  {
                if ($page < 5) {
                    for ($counter = 0 ; $counter < 6 ; $counter++) {
                        if ($counter>0) {
                            $pageination .= ' - ';
                        }

                        if ($counter == $page) {
                            $pageination .= '<b><font size="2">' . ($counter + 1) . '</font></b>';
                        }
                        else {
                            $pageination .= '<a href="' . $url . '/page/' . $counter . '">' . ($counter + 1) . '</a>';
                        }

                    }

                    $pageination .= ' ... - ';
                    $pageination .= '<a href="' . $url . '/page/' . ($pages - 2) . '">' . ($pages - 1) . '</a>';
                    $pageination .= ' - ';
                    $pageination .= '<a href="' . $url . '/page/' . ($pages - 1) . '">' . ($pages) . '</a>';
                }
                else {
                    if ( ($page < $pages-5) && ($page > 4)) {
                        $pageination .= '<a href="' . $url . '/page/0">1</a>';
                        $pageination .= ' - ';
                        $pageination .= '<a href="' . $url . '/page/1">2</a>';
                        $pageination .= ' - ';
                        $pageination .= ' ... ';

                        for ($counter = $page-2; $counter < $page+3 ; $counter++) {
                            $pageination .= ' - ';
                            if($counter == $page) {
                                $pageination .= '<b><font size="2">' . ($counter + 1) . '</font></b>';
                            }
                            else {
                                $pageination .= '<a href="' . $url . '/page/' . $counter . '">' . ($counter + 1) . '</a>';
                            }
                        }
                        
                        $pageination .= ' - ... - ';
                        $pageination .= '<a href="' . $url . '/page/' . ($pages - 2) . '">' . ($pages - 1) . '</a>';
                        $pageination .= ' - ';
                        $pageination .= '<a href="' . $url . '/page/' . ($pages - 1) . '">' . ($pages) . '</a>';
                    }
                    else {
                        $pageination .= '<a href="' . $url . '/page/0">1</a>';
                        $pageination .= ' - ';
                        $pageination .= '<a href="' . $url . '/page/1">2</a>';
                        $pageination .= ' - ';
                        $pageination .= ' ... ';

                        for ($counter = $pages-6; $counter < $pages ; $counter++) {
                            $pageination .= ' - ';

                            if($counter == $page) {
                                $pageination .= '<b><font size="2">' . ($counter + 1) . '</font></b>';
                            }
                            else {
                                $pageination .= '<a href="' . $url . '/page/' . $counter . '">' . ($counter + 1) . '</a>';
                            }
                        }
                    }
                }
            }

            if($pages > 9) {
                if ($page < $pages - 1) {
                    $pageination .= ' <a href="' . $url . '/page/' . ($page+1) . '">&gt;&gt;</a> ';
                }
                else {
                    $pageination .= ' &gt;&gt; ';
                }

                if ( $page < $pages-10 ) {
                    $pageination .= ' <a href="' . $url . '/page/' . ($page+10) . '">| +10</a> ';
                }
                else {
                    $pageination .= ' | +10 ';
                }
            }
        }
        
        return $pageination;
    }
}