<?php

/**
 * Description of Statistics
 *
 * @author mlaug
 */
class Yourdelivery_Statistics {

    /**
     * The grid based on the given view
     * @var Bvb_Grid_Deploy_Table
     */
    protected $_grid = null;

    /**
     * The coordinates
     * @var string
     */
    protected $_x = null;
    protected $_y = null;

    /**
     * The x label
     * @var string
     */
    protected $_xLabel = null;

    /**
     * The flash parameters
     * http://www.fusioncharts.com/free/docs/
     * @var string
     */
    protected $_flashParams = null;

    /**
     * The flash default parameters
     * @var string
     */
    protected $_flashDefaults = null;

    /**
     * The flash type
     * @var string
     */
    protected $_flashSwf = null;

    /**
     * Enable/disbale grig
     * @var string
     */
    protected $_gridEnabled = true;

    /**
     * The flash info
     * @var string
     */
    protected $_flashInfo = "";

    /**
     * filters to apply
     * @var array
     */
    protected $_filter = null;
    protected $_timeFilterEnabled = false;
    protected $_plzFilterEnabled = false;
    protected $_cityFilterEnabled = false;
    protected $_preselection = null;
    protected $_setFilters = null;

    const INTERVAL_TIME = 0;

    const INTERVAL_INT = 1;

    /**
     * Create statistics by given view
     * @since 11.10.2010
     * @author mlaug
     * @param string $view
     * @return void
     */
    public function __construct($view) {

        $this->_view = $view;

        // initialize select
        $db = Zend_Registry::get('dbAdapter');
        try {
            //setup zend select base on given view
            $select = $db->select()
                            ->from($view);
        } catch (Exception $e) {
            //could not find view in database
        }

        // initialize the grid
        $this->_grid = Default_Helper::getTableGrid();
        $this->_grid->setSource(new Bvb_Grid_Source_Zend_Select($select));

        // initialize flash
        $this->_flashParams = array();
        $this->_flashDefaults = array(
            'animation' => 1,
            'rotateNames' => 0,
            'decimalSeparator' => ",",
            'thousandSeparator' => ".",
            'decimalPrecision' => 0,
            'formatNumberScale' => 0,
        );
    }

    public function getGrid() {
        return $this->_grid;
    }

    /**
     * Disable grid
     * @author vpriem
     * @since 03.11.2010
     * @return Yourdelivery_Statistics
     */
    public function disableGrid(){
        $this->_gridEnabled = false;
        return $this;
    }

    /**
     * Disable grid
     * @author vpriem
     * @since 03.11.2010
     * @return Yourdelivery_Statistics
     */
    public function enableGrid(){
        $this->_gridEnabled = true;
        return $this;
    }

    //FILTERS

    public function setFilterSelected ($params) {
        $this->_preselection = $params;
    }

    /**
     * @author
     * @since
     * @return Yourdelivery_Statistics
     */
    public function setFilter ($type, $column, $operands = false) {

        if (!in_array($type, array('dropdown', 'radio', 'select'))) {
            return $this;
        }

        // select distinct * from column

        switch ($type) {
            case 'dropdown': {
                    // get distinct values from column
                    $source = $this->_grid->getSource()->execute();
                    if (is_null($source[0][$column])) {
                        return $this;
                    }

                    $values = array();
                    foreach ($source as $key => $value) {
                        if (!in_array($source[$key][$column], $values)) {
                            $values[] = $source[$key][$column];
                        }
                    }
                    sort($values);

                    // get URL params
                    $params = json_decode($this->_preselection['filtersgrid']);
                    $filterString = 'filter_';
                    $filterString .= $column;
                    $vals = $params->$filterString;

                    $valOperands = null;
                    $valFilter = null;
                    if ($operands) {
                        $valOperands = substr($vals, 0, 1);
                        $valFilter = substr($vals, 1);
                    } else {
                        $valFilter = $vals;
                    }

                    // create html for filter
                    $html = '<div class="item">
                            <div class="item-head">
                                <a class="yd-plus"></a>
                                <a class="yd-minus"></a>
                                ' . ucfirst($column) . '
                            </div>
                            <div class="item-content">
                                <form>
                                    <span>';

                    $html .= '<select id="filter-custom-' . $column . '">
                            <option value="">Bitte w&auml;hlen ...</option>';
                    foreach ($values as $val) {
                        $html .= '<option value="' . $val . '" ';
                        if ($val == $valFilter) {
                            $html .= 'selected';
                        }
                        $html .= '>' . $val . '</option>';
                    }
                    $html .= '</select>';

                    if ($operands) {
                        $html .= '<label><input type="radio" name="addition-' . $column . '" value=">" ';
                        if ($valOperands == '>') {
                            $html .= 'checked';
                        }
                        $html .= '/> gr&ouml;&szlig;er</label>
                              <label><input type="radio" name="addition-' . $column . '" value="<" ';
                        if ($valOperands == '<') {
                            $html .= 'checked';
                        }
                        $html .= '/> kleiner</label>
                              <label><input type="radio" name="addition-' . $column . '" value="=" ';
                        if ($valOperands == '=') {
                            $html .= 'checked';
                        }
                        $html .= '/> gleich</label>';
                    }


                    $html .= '</span>
                                </form>
                            </div>
                        </div>';
                    $this->_setFilters .= $html;

                    // create javascript for this filter
?>
                    <script type="text/javascript">
                        <!--
                        $(document).ready(function(){
                            if( $('#filter_grid<? echo $column ?>').length > 0){
                                $('#filter_grid<? echo $column ?>').hide();
                            }

                            $('#filter-custom-<? echo $column ?>').live('change', function(){
                                var operand = $('input[name=addition-<? echo $column ?>]:checked').val();
                                if( operand == undefined ){
                                    operand = '=';
                                }
                                $('#filter_grid<? echo $column ?>').val(operand+$(this).val()).change();
                            });
                        });
                        -->
                    </script>
<?
                    // TODO: Filter should have choosen value after reload

                    break;
                }

            default:
                break;
        }

        return $this;
    }

    /**
     * set filter to every column
     */
    private function setStandardFilters() {
        $source = $this->_grid->getSource()->execute();

        $filters = new Bvb_Grid_Filters();
        foreach ($source[0] as $column => $content) {
            $filters->addFilter($column);
        }
        $this->_grid->addFilters($filters);
    }

    /**
     * set filter for time automaticly if column exists in view
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 20.10.2010
     */
    private function enableTimeFilter() {
        // check if column `time` exists in view
        $source = $this->_grid->getSource()->execute();
        if (!is_null($source[0]['time'])) {
?>
            <script type="text/javascript">
                <!--
                $(document).ready(function(){
                    if( $('#filter_gridtime').length > 0){
                        $('#filter_gridtime').hide();
                        // get right time an initiate the datepickers
                        var gridTime = $('#filter_gridtime').val();
                        var fromDate  = gridTime.split('<>')[0];
                        var untilDate = gridTime.split('<>')[1];
                        $('#filter-date-from').val(fromDate);
                        $('#filter-date-until').val(untilDate);

                        initDatepicker('raw', 'filter-date-from', fromDate);
                        initDatepicker('raw', 'filter-date-until', untilDate);
                    }

                    $('#filter-date-submit').live('click', function(){
                        var from = $('#filter-date-from').val();
                        var until = $('#filter-date-until').val();
                        $('#filter_gridtime').val(from+'<>'+until);
                        $('#filter_gridtime').change();
                    });
                });
                -->
            </script>
<?
            $this->_timeFilterEnabled = true;
        } else {
            $this->_timeFilterEnabled = false;
        }
    }

    /**
     * set filter for PLZ automaticly if column exists in view
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 20.10.2010
     */
    private function enablePlzFilter() {
        $source = $this->_grid->getSource()->execute();
        if (!is_null($source[0]['plz'])) {
?>
            <script type="text/javascript">
                <!--
                $(document).ready(function(){
                    if( $('#filter_gridplz').length > 0){
                        $('#filter_gridplz').hide();
                    }

                    $('#filter-plz-submit').live('click', function(){
                        // get values from multiselect
                        var filterplz = $('#filter-plz-select').val();
                        $('#filter_gridplz').val(filterplz).change();
                    });
                });
                -->
            </script>
<?
            $this->_plzFilterEnabled = true;
        } else {
            $this->_plzFilterEnabled = false;
        }
    }

    /**
     * set filter for city automaticly if column exists in view
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 20.10.2010
     */
    private function enableCityFilter() {
        $source = $this->_grid->getSource()->execute();
        if (!is_null($source[0]['city'])) {
?>
            <script type="text/javascript">
                <!--
                $(document).ready(function(){
                    if( $('#filter_gridcity').length > 0){
                        $('#filter_gridcity').hide();
                    }

                    if($('#filter-plz-select').length > 0){
                        $('#filter-city-dropdown').live('change', function(){
                            var city = $(this).val();
                            // ajax get plzs by ort
                            $('#filter-plz-select-span').html('warten!');
                            $.ajax({
                                type: "POST",
                                url: "/request_administration/plzsbyort",
                                data: ({ 'ort' : city }),
                                success: function(html){
                                    // select plzs
                                    $('#filter-plz-select-span').html(html);
                                }
                            });
                        });
                    };

                    $('#filter-city-submit').live('click', function(){
                        // get values from multiselect
                        var filtercity = $('#filter-city-select').val();
                        $('#filter_gridcity').val(filtercity).change();
                    });
                });
                -->
            </script>
<?
            $this->_cityFilterEnabled = true;
        } else {
            $this->_cityFilterEnabled = false;
        }
    }

    /**
     * get html of specified filters
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 20.10.2010
     * @return string html
     */
    public function setFilters() {
        echo $this->_setFilters;
    }

    /**
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 20.10.2010
     * @return void
     */
    public function applyFilters() {

        $this->setStandardFilters();
        $this->enableTimeFilter();
        $this->enablePlzFilter();
        $this->enableCityFilter();
    }

    //OPENFLASH
    /**
     * return a json string
     * @author vpriem
     * @since 11.10.2010
     * @return string
     */
    private function getFlashData() {

        if ($this->_flashSwf != null) {
            // must be replaced with to get same result as grid displays
            $results = $this->_grid->getSource()->execute();
//            $select = $this->grid->getSource()->getSelectObject();

            $data = "<graph";
            foreach ($this->_flashParams as $p => $v) {
                $data .= " " . $p . "='" . $v . "'";
            }
            $data .= '>';

            if ($this->_x !== null) {
                foreach ($results as $result) {
                    $data .= "<set value='" . str_replace("&", "", $result[$this->_x]) . "'";
                    if ($this->_xLabel !== null) {
                        $data .= " name='" . str_replace("&", "", $result[$this->_xLabel]) . "'";
                    }
                    $data .= " />";
                }
            }

            return $data . "</graph>";
        }
        return "";
    }

    /**
     * @author vpriem
     * @since 12.10.2010
     * @return Yourdelivery_Statistics
     */
    public function setXY($x, $y) {

        $this->_x = $x;
        $this->_y = $y;
        return $this;
    }

    /**
     * @author vpriem
     * @since 12.10.2010
     * @return Yourdelivery_Statistics
     */
    public function setX($x) {
        $this->_x = $x;
        return $this;
    }

    /**
     * @author vpriem
     * @since 12.10.2010
     * @return Yourdelivery_Statistics
     */
    public function setY($y) {
        $this->_y = $y;
        return $this;
    }

    /**
     * @author vpriem
     * @since 12.10.2010
     * @return Yourdelivery_Statistics
     */
    public function setLabel($label) {

        $this->_xLabel = $label;
        return $this;
    }

    /**
     * Render as line
     * using openflash
     * @author vpriem
     * @since 12.10.2010
     * @return Yourdelivery_Statistics
     */
    public function renderAsBar($caption = "Statistiken", $xName = "", $yName = "", $params = null) {

        $this->_flashSwf = "Column2D";

        $this->_flashParams = array_merge($this->_flashDefaults, array(
                    'caption' => $caption,
                    'xAxisName' => $xName,
                    'yAxisName' => $yName,
                ));

        if ($params !== null) {
            parse_str($params, $params);
            $this->_flashParams = array_merge($this->_flashParams, $params);
        }

        return $this;
    }

    public function renderAsBar3D($caption = "Statistiken", $xName = "", $yName = "", $params = null) {

        $this->renderAsBar($caption, $xName, $yName, $params);
        $this->_flashSwf = "Column3D";
        return $this;
    }

    /**
     * Render as pie
     * using openflash
     * @author vpriem
     * @since 11.10.2010
     * @return Yourdelivery_Statistics
     */
    public function renderAsPie($caption = "Statistiken", $xName = "", $yName = "", $params = null) {

        $this->_flashSwf = "Pie2D";

        $this->_flashParams = array_merge($this->_flashDefaults, array(
                    'caption' => $caption,
                    'xAxisName' => $xName,
                    'yAxisName' => $yName,
                    'showValues' => 0,
                    'shownames' => 1,
                ));

        if ($params !== null) {
            parse_str($params, $params);
            $this->_flashParams = array_merge($this->_flashParams, $params);
        }

        return $this;
    }

    public function renderAsPie3D($caption = "Statistiken", $xName = "", $yName = "", $params = null) {

        $this->renderAsBar($caption, $xName, $yName, $params);
        $this->_flashSwf = "Pie3D";
        return $this;
    }

    /**
     * Render as line
     * using openflash
     * @author vpriem
     * @since 11.10.2010
     * @return Yourdelivery_Statistics
     */
    public function renderAsLine($caption = "Statistiken", $xName = "", $yName = "", $params = null) {

        $this->_flashSwf = "Line";

        $this->_flashParams = array_merge($this->_flashDefaults, array(
                    'caption' => $caption,
                    'xAxisName' => $xName,
                    'yAxisName' => $yName,
                    'showValues' => 0,
                    'shownames' => 1,
                    'showColumnShadow' => 1,
                    'showAlternateHGridColor' => 1,
                ));

        if ($params !== null) {
            parse_str($params, $params);
            $this->_flashParams = array_merge($this->_flashParams, $params);
        }

        return $this;
    }

    /**
     * Set flash info
     * @author vpriem
     * @since 03.11.2010
     * @return Yourdelivery_Statistics
     */
    public function setFlashInfo ($info) {

        $this->_flashInfo = $info;
        return $this;
        
    }

    /**
     * Get flash info
     * @author vpriem
     * @since 03.11.2010
     * @return string
     */
    public function getFlashInfo(){

        return $this->_flashInfo;

    }

    //OUTPUT METHODS
    /**
     * print out everythin by calling printAll
     * @author mlaug
     * @since 13.10.2010
     * @return string
     */
    public function __toString() {

        return $this->printAll();
    }

    /**
     * print out everything
     * @author mlaug
     * @since 13.10.2010
     * @return string
     */
    public function printAll() {

        // get the grid first to build the select
        $grid = $this->printGrid();
        return '<div class="statistics">' . $this->printFlash() . $this->printFilter() . '</div>' . ($this->_gridEnabled ? $grid : "");
        
    }

    /**
     * get script of grid
     * and generate our script depending on filters
     * @author mlaug
     * @since 13.10.2010
     * @return stirng
     */
    public function getHeaderScript() {
        //via export, this function is not available
        if (method_exists($this->_grid, 'getHeaderScript')) {
            return $this->_grid->getHeaderScript();
        }
        // create our scripts
        return '';
    }

    /**
     * print out grid
     * @author mlaug
     * @since 13.10.2010
     * @return string
     */
    public function printGrid() {

        $this->applyFilters();
        ob_start();
        ?><div class="one-column-box">

            <div class="item">
                <div class="item-head">
                    <a class="yd-plus"></a>
                    <a class="yd-minus"></a>
                    Item Head
                </div>
                <div class="item-content"><?php echo $this->_grid->deploy(); ?></div>
            </div>
        </div><?php
        return ob_get_clean();
    }

    /**
     * @author
     * @since
     * @return string
     */
    public function printFilter() {

        ob_start();
        ?><div class="three-column-box">

            <div class="statistics-info">
                INFO
            </div>

            <? $this->setFilters(); ?>

        </div>

        <div class="two-column-box">

            <div class="statistics-info">
                INFO
            </div>

            <div class="item">
                <div class="item-head">
                    <a class="yd-plus"></a>
                    <a class="yd-minus"></a>
                    Zeitraum
                </div>
                <div class="item-content">
                <? if ($this->_timeFilterEnabled) : ?>
                    <span class="statistics-date">
                        Von
                        <input type="text" value="" id="filter-date-from" />
                        bis
                        <input type="text" value="" id="filter-date-until" />
                        <a class="cursor" id="filter-date-submit">anwenden</a>
                    </span>
                <? endif; ?>
                </div>
            </div>

            <div class="item">
                <div class="item-head">
                    <a class="yd-plus"></a>
                    <a class="yd-minus"></a>
                    Ort
                </div>
                <div class="item-content">
                    <span class="statistics-plz">
                    <? if ($this->_plzFilterEnabled) : ?>
                        <span id="filter-plz-select-span">
                            PLZ
                            <select multiple id="filter-plz-select" size="3">
                            <?
                            $plzs = Yourdelivery_Model_DbTable_City::allPlz();
                            foreach ($plzs as $plz) {
                                echo '<option value="' . $plz['plz'] . '">' . $plz['plz'] . '</option>';
                            }
                            ?>
                        </select>
                        <a class="cursor" id="filter-plz-submit">anwenden</a>
                    </span>
                    <? endif; ?>
                    <? if ($this->_cityFilterEnabled) : ?>
                            Stadt
                            <select id="filter-city-dropdown">
                        <?
    //                                    $orte = Yourdelivery_Model_DbTable_Orte::getAllCities();
    //                                    foreach ($orte as $ort) {
    //                                        echo '<option value="'.$ort.'">'.$ort.'</option>';
    //                                    }
                        ?>
                            <option >Ort wählen</option>
                            <option value="Berlin">Berlin</option>
                            <option value="Hamburg">Hamburg</option>
                            <option value="München">München</option>
                            <option value="Frankfurt am Main">Frankfurt am Main</option>
                        </select>
                        <a class="cursor" id="filter-city-submit">anwenden</a>
                    <? endif; ?>
                    </span>
                </div>
            </div>

        </div><?php
        return ob_get_clean();
    }

    /**
     * @author vpriem
     * @since 11.10.2010
     * @return string
     */
    public function printFlash() {

        ob_start();
        if ($this->_flashSwf != null) {
        ?><div class="one-column-box">

            <div class="statistics-info"><?php echo $this->getFlashInfo(); ?></div>

                <div class="item">
                    <div class="item-head">
                        <a class="yd-plus"></a>
                        <a class="yd-minus"></a>
                    </div>
                    <div class="item-content">
                        <object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000"
                                codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0"
                                width="900" height="300" id="flash-fusion-charts" align="middle">
                            <param name="movie" value="/media/flash/fusion-charts/FCF_<?php echo $this->_flashSwf; ?>.swf?chartWidth=900&chartHeight=300" />
                            <param name="quality" value="high" />
                            <embed src="/media/flash/fusion-charts/FCF_<?php echo $this->_flashSwf; ?>.swf?chartWidth=900&chartHeight=300"
                                   FlashVars="&dataXML=<?php echo $this->getFlashData(); ?>"
                                   quality="high"
                                   bgcolor="#FFFFFF"
                                   width="900"
                                   height="300"
                                   name="flash-fusion-charts"
                                   align="middle"
                                   allowScriptAccess="sameDomain"
                                   type="application/x-shockwave-flash"
                                   pluginspage="http://www.macromedia.com/go/getflashplayer" />
                        </object>
                    </div>
                </div>

            </div><?php
        }
        return ob_get_clean();

    }

    // EXPORT METHODS

    /**
     * export current data as csv file
     * @author mlaug
     * @since 22.10.2010
     * @return string
     */
    public function exportAsCsv() {

        $results = (array) $this->_grid->getSource()->execute();
        $cols = array_keys(current($results));
        $csv = new Default_Exporter_Csv();
        $csv->addCols($cols);
        foreach ($results as $result) {
            $csv->addRow($result);
        }
        return $csv->save();

    }

    /**
     * export current data as xls (excel) file
     * @author mlaug
     * @since 22.10.2010
     * @return string
     * @todo: find a parser for xls and implement
     */
    public function exportAsXls() {

    }

}
