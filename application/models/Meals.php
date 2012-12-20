<?php

class Yourdelivery_Model_Meal_Ratings {

    protected $_meal = null;
    protected $_data = null;

    public function getCount() {
        return $this->getData()->count();
    }

    /**
     * add a new rating
     * @author Matthias Laug <laug@lieferando.de>
     * @since 28.06.2011
     * @param integer $rating
     * @param string $comment
     * @return boolean
     */
    public function addRating(Yourdelivery_Model_Order $order, $rating, $comment) {
        if ($rating < 0 || $rating > 10) {
            return false;
        }

        $table = new Yourdelivery_Model_DbTable_Meal_Ratings();
        if (!$this->isRated($order)) {
            $table->createRow(
                    array(
                        'orderId' => $order->getId(),
                        'rating' => $rating,
                        'mealId' => $this->_meal->getId(),
                        'comment' => $comment
                    )
            )->save();
            return true;
        } else {
            $row = $table->getRating($order->getId(), $this->_meal->getId());
            $row->rating = $rating;
            $row->comment = $comment;
            $row->save();
            return true;
        }
        return false;
    }

    public function isRated(Yourdelivery_Model_Order $order) {
        $table = new Yourdelivery_Model_DbTable_Meal_Ratings();
        return $table->isRated($order->getId(), $this->getMeal()->getId());
    }

    /**
     * set current meal
     * @author Matthias Laug <laug@lieferando.de>
     * @since 28.06.2011
     * @param Yourdelivery_Model_Meals $meal 
     */
    public function setMeal(Yourdelivery_Model_Meals $meal) {
        $this->_meal = $meal;
    }

    /**
     * get current meal
     * @author Matthias Laug <laug@lieferando.de>
     * @since 28.06.2011
     * @return Yourdelivery_Model_Meals
     */
    public function getMeal() {
        return $this->_meal;
    }

    /**
     * get all ratings data from meal
     * @author Matthias Laug <laug@lieferando.de>
     * @since 28.06.2011
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getData() {
        if ($this->_data === null) {
            $this->_data = $this->_meal->getTable()->getRatings();
        }
        return $this->_data;
    }

    /**
     * @author Daniel Hahn <hahn@lieferando.de>
     * @since 15.11.2011
     * @param type $orderId
     * @return mixed
     */
    public function getRatingForOrder($orderId) {

        $data = $this->getData();

        foreach ($data as $entry) {
            if ($entry->orderId == $orderId) {
                return $entry->rating;
            }
        }

        return false;
    }

}

/**
 * Description of Meals
 * @package service
 * @subpackage menu
 * @author Matthias Laug <laug@lieferando.de>
 */
class Yourdelivery_Model_Meals extends Default_Model_Base {

    /**
     * stores current size
     * @var int
     */
    protected $_curSize = null;

    /**
     * store the current size name
     * @var string
     */
    protected $_curSizeName = null;

    /**
     * stores currently assigned extras during ordering process
     * @var SplObjectStorage
     */
    protected $_curExtras = null;

    /**
     * stores currently assigned options during ordering process
     * @var SplObjectStorage
     */
    protected $_curOptions = null;
    
    /**
     * current costs by set size
     * @var int
     */
    protected $_curCost = null;

    /**
     * current taxes
     * @var int
     */
    protected $_curTax = null;

    /**
     * all extras append to this meal
     * @var array
     */
    protected $_extras = array();

    /**
     * all extras append to this meal, but stored as array not as object
     * @var array
     */
    protected $_extrasFast = array();

    /**
     * all options append to this meal
     * @var array
     */
    protected $_options = null;

    /**
     * Get all possible attributes of meals
     * @author Alex Vait <vait@lieferando.de>
     * @since 27.06.2012
     * @return array
     */
    static function getAllAttributes() {
        return array(
            'bio' => __b('Bio'),
            'fish' => __b('Fisch'),
            'glutenfree' => __b('Glutenfrei'),
            'halal' => __b('Halal'),
            'kosher' => __b('Koscher'),
            'garlic' => __b('Knoblauch'),
            'spicy' => __b('Scharf'),
            'vegan' => __b('Vegan'),
            'vegetarian' => __b('Vegetarisch')
        );
    }    
    
    public function __construct($id = null) {

        parent::__construct($id);
        //create a directory in storage, so that we cant store images later
        if (!is_null($id)) {
            //maybe we store some meal pictures here
            $this->getStorage()->setSubFolder('restaurants/' . $this->producedBy()->getId() . '/meals/' . $this->getId());
        }
    }
    
    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 08.02.2011
     * @param float $taxtype
     * @return float
     */
    public function getTax($taxtype = ALL_TAX) {
        $tax = $this->getMwst();
        $result = array(
            ALL_TAX => 0
        );

        //build tax array with all available tax types
        foreach ($this->config->tax->types as $type) {
            $result[$type] = 0;
        }

        try {
            $brutto = $this->getCost();
            $taxAmount = $brutto - ($brutto / (100 + $tax) * 100);

            $result[$tax] += $taxAmount;
            $result[ALL_TAX] += $taxAmount;

            foreach ($this->getCurrentExtras() as $extra) {
                $eTax = $extra->getMwst();
                $eBrutto = $extra->getCost();
                $result[$eTax] += $eBrutto - ( $eBrutto / (100 + $eTax) * 100 );
                $result[ALL_TAX] += $eBrutto - ( $eBrutto / (100 + $eTax) * 100 );
            }

            foreach ($this->getCurrentOptions() as $option) {
                $oTax = $option->getMwst();
                $oBrutto = $option->getCost();
                $result[$oTax] += $oBrutto - ( $oBrutto / (100 + $oTax) * 100 );
                $result[ALL_TAX] += $oBrutto - ( $oBrutto / (100 + $oTax) * 100 );
            }

            return $result[$taxtype];
        } catch (Yourdelivery_Exception_NoSizeSetForMeal $e) {
            return array();
        }
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 08.02.2011
     * @param float $taxtype
     * @return float
     */
    public function getItem($taxtype = ALL_TAX) {
        $brutto = $this->getAllCosts($taxtype);
        $tax = $this->getTax($taxtype);
        return $brutto - $tax;
    }

    /**
     * @deprecated
     * @return float
     */
    public function getTax7() {
        return $this->getTax(7);
    }

    /**
     * @deprecated
     * @return float
     */
    public function getTax19() {
        return $this->getTax(19);
    }

    /**
     * @deprecated
     * @return float
     */
    public function getItem19() {
        return $this->getItem(19);
    }

    /**
     * @deprecated
     * @return float
     */
    public function getItem7() {
        return $this->getItem(7);
    }

    /**
     * get the category of meal
     * @author Matthias Laug <laug@lieferando.de>
     * @return Yourdelivery_Model_Meal_Category
     */
    public function getMealCategory() {
        return new Yourdelivery_Model_Meal_Category($this->getCategoryId());
    }

    /**
     * get the category name
     * @author Matthias Laug <laug@lieferando.de>
     * @return string
     */
    public function getCategoryName() {
        return $this->getTable()->getCategoryName();
    }

    /**
     * get amount of taxes from category
     * @author Matthias Laug <laug@lieferando.de>
     * @return int
     */
    public function getCategoryMwst() {
        return $this->getTable()->getCategoryMwst();
    }

    /**
     * get description of category
     * @author Matthias Laug <laug@lieferando.de>
     * @return string
     */
    public function getCategoryDescription() {
        return $this->getTable()->getCategoryDescription();
    }

    /**
     * get meals category
     * @author Matthias Laug <laug@lieferando.de>
     * @return Yourdelivery_Model_Meal_Category
     */
    public function getCategory() {
        return new Yourdelivery_Model_Meal_Category($this->getTable()->getCategoryId());
    }

    /**
     * get meals category id
     * @author Matthias Laug <laug@lieferando.de>
     * @return int
     */
    public function getCategoryId() {
        return $this->getTable()->getCategoryId();
    }

    /**
     * get all sizes this meal is available in
     * @author Matthias Laug <laug@lieferando.de>
     * @return array
     */
    public function getSizes() {
        return $this->getTable()->getSizes();
    }

    /**
     * is this meal delivered in this sizeId ?
     * @author alex
     * @return bool
     */
    public function hasSize($sizeId) {
        if ($this->getTable()->getSize($sizeId)) {
            return true;
        }

        return false;
    }

    /**
     * get cost of meal of certain size
     * @author alex
     * @return int
     */
    public function getCostForSize($sizeId) {
        return $this->getTable()->getCostForSize($sizeId);
    }

    /**
     * get cost of pfand of certain size
     * @author alex
     * @return int
     */
    public function getPfandForSize($sizeId) {
        return $this->getTable()->getPfandForSize($sizeId);
    }

    /**
     * get nr of size-meal association
     * @author Alex Vait <vait@lieferando.de>
     * @since 20.12.2011
     * @return string
     */
    public function getNrForSize($sizeId) {
        $rel = $this->getTable()->getSizeRelation($sizeId);
        return $rel['nr'];
    }

    /**
     * get certain size of the meal
     * @author alex
     * @return int
     */
    public function getSize($sizeId) {
        return $this->getTable()->getSize($sizeId);
    }

    /**
     * get cost of meal
     * @author Matthias Laug <laug@lieferando.de>
     * @return int
     */
    public function getCost() {
        //if any cost have been set, we do not check for
        //current settings in database
        if (is_null($this->_curCost)) {
            
            if ($this->getPriceType() != 'normal') {
                $this->_curCost = 0;
            } else {
                if (is_null($this->getCurrentSize())) {
                    throw new Yourdelivery_Exception_NoSizeSetForMeal();
                }

                $sizes = $this->getSizes();

                if (!array_key_exists($this->getCurrentSize(), $sizes)) {
                    throw new Yourdelivery_Exception_NoSizeSetForMeal();
                }

                $this->_curCost = $sizes[$this->getCurrentSize()]['cost'];
            }
        }

        return $this->_curCost;
    }

    /**
     * get sum of meal+option+extras costs
     * @author Matthias Laug <laug@lieferando.de>
     * @return int
     */
    public function getAllCosts($taxtype = ALL_TAX) {
        $total = 0;

        //check if the given taxtype is available
        if ($taxtype == ALL_TAX || $this->getMwst() == $taxtype) {
            $total = $this->getCost();
        }

        foreach ($this->getCurrentOptions() as $opt) {
            if ($taxtype == ALL_TAX || $opt->getMwst() == $taxtype) {
                $total += $opt->getCost();
            }
        }
        foreach ($this->getCurrentExtras() as $ext) {
            if ($taxtype == ALL_TAX || $ext->getMwst() == $taxtype) {
                $total += $ext->getCost();
            }
        }
        return $total;
    }

    public function setPfand($pfand) {
        $this->_curPfand = $pfand;
    }

    /**
     * get pfand of this item if any
     * @author Matthias Laug <laug@lieferando.de>
     * @return int
     */
    public function getPfand() {
        //if any pfand has been set we do not look up in database
        if (is_null($this->_curPfand)) {
            if (is_null($this->getCurrentSize())) {
                throw new Yourdelivery_Exception_NoSizeSetForMeal();
            }

            $sizes = $this->getSizes();

            if (!array_key_exists($this->getCurrentSize(), $sizes)) {
                throw new Yourdelivery_Exception_NoSizeSetForMeal();
            }

            $this->_curPfand = $sizes[$this->getCurrentSize()]['pfand'];

            if ($this->_curPfand == 0) {
                $this->_curPfand = false;
            }
        }

        return $this->_curPfand;
    }

    /**
     * set current cost
     * @author alex
     * @param int $cost
     */
    public function setCost($cost) {
        $this->_curCost = $cost;
    }

    /**
     * set current tax
     * @author alex
     * @param int $tax
     */
    public function setCurrentTax($tax) {
        $this->_curTax = $tax;
    }

    /**
     * get current tax
     * @author Matthias Laug <laug@lieferando.de>
     * @return int
     */
    public function getCurrentTax() {
        return $this->_curTax;
    }

    /**
     * set current size
     * @todo: maybe use an opject here too
     * @author Matthias Laug <laug@lieferando.de>
     * @param int $sizeId
     */
    public function setCurrentSize($sizeId) {
        $sizes = $this->getSizes();
        if (!is_array($sizes) || !array_key_exists($sizeId, $sizes)) {
            throw new Yourdelivery_Exception_Database_Inconsistency(sprintf('could not add size %d to meal %d', $sizeId, $this->getId()));
        }
        $this->clearData();
        $this->getTable()->clearData();
        $this->_curSize = (int) $sizeId;
    }

    /**
     * set current size by name
     * @author Jens Naie <naie@lieferando.de>
     * @param string $sizeId
     */
    public function setCurrentSizeByName($sizeName) {
        $sizes = $this->getSizes();
        if (!is_array($sizes)) {
            throw new Yourdelivery_Exception_Database_Inconsistency(sprintf('could not add size %s to meal %d', $sizeName, $this->getId()));
        }
        foreach ($sizes as $size) {
            if (trim($sizeName) == trim($size['name'])) {
                $this->clearData();
                $this->getTable()->clearData();
                $this->_curSize = (int) $size['id'];
                return;
            }
        }
        throw new Yourdelivery_Exception_Database_Inconsistency(sprintf('could not add size %s to meal %d', $sizeName, $this->getId()));
    }

    /**
     * get current size
     * @author Matthias Laug <laug@lieferando.de>
     * @return int
     */
    public function getCurrentSize() {
        return $this->_curSize;
    }

    /**
     * get current size as string
     * @author Matthias Laug <laug@lieferando.de>
     * @return string
     */
    public function getCurrentSizeName() {
        if (is_null($this->_curSizeName)) {
            $this->_curSizeName = $this->getTable()->getSizeName(
                    $this->getCurrentSize()
            );
        }
        return $this->_curSizeName;
    }

    /**
     * get all options for meal
     * @author Matthias Laug <laug@lieferando.de>
     * @return Yourdelivery_Model_Meal_Option
     */
    public function hasOptions() {
        if (count($this->getTable()->getOptions()) > 0) {
            return true;
        }
        return false;
    }

    /**
     * check if an extra is currently appended
     * @author Matthias Laug <laug@lieferando.de>
     * @since 25.08.2010
     * @param int $id
     * @return boolean
     */
    public function hasCurrentExtraAppend($id) {
        $extras = $this->getCurrentExtras();
        foreach ($extras as $extra) {
            if (is_object($extra) && $extra->getId() == $id) {
                return true;
            }
        }
        return false;
    }

    /**
     * check if an option is currently appended
     * @author Matthias Laug <laug@lieferando.de>
     * @since 25.08.2010
     * @param int $id
     * @return boolean
     */
    public function hasCurrentOptionAppend($id, $size = null) {
        $options = $this->getCurrentOptions();
        foreach ($options as $option) {
            if (is_object($option) && $option->getId() == $id) {
                if (!$size || $size == $this->getCurrentSize()) {
                    return true;
                } else {
                    return false;
                }
            }
        }
        return false;
    }

    /**
     * checks if the option is available for this meal
     * @author Matthias Laug <laug@lieferando.de>
     * @return boolean
     */
    public function hasOption($searchedOptionId) {
        $optionRows = $this->getOptions();

        foreach ($optionRows as $row) {
            foreach ($row->getOptions() as $option) {
                if ($option->getId() == $searchedOptionId) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * checks if the option group is available for this meal
     * @author Matthias Laug <laug@lieferando.de>
     * @return boolean
     */
    public function hasOptionsRow($rowId) {
        return $this->getTable()->hasOptionsRow($rowId);
    }

    /**
     * check if there are any extras
     * @author Matthias Laug <laug@lieferando.de>
     * @since 10.11.2011
     * @return boolean
     */
    public function hasAnyExtras() {
        $sizes = $this->getSizes();
        foreach ($sizes as $size) {
            if ($this->hasExtras((integer) $size['id'])) {
                return true;
            }
        }
        return false;
    }

    /**
     * check if this meal has some extras
     * @author Matthias Laug <laug@lieferando.de>
     * @return boolean
     */
    public function hasExtras($sizeId = null) {
        $sid = is_null($sizeId) ? (integer) $this->getCurrentSize() : (integer) $sizeId;
        
        if (!is_null($this->_extras) && !is_null($this->_extras[$sid]) && $this->_extras[$sid] instanceof SplObjectStorage) {
            if ($this->_extras[$sid]->count() > 0) {
                return true;
            } else {
                return false;
            }
        }
        
        return count($this->getExtras($sid)) > 0;
    }

    /**
     * meal-size-extra association exists?
     * @author alex
     * @return boolean
     */
    public function hasExtra($extraId, $sizeId) {
        return $this->getTable()->hasExtra($extraId, $sizeId);
    }

    /**
     * cost for certain extra in this size
     * @author alex
     * @return int
     */
    public function getExtraCost($extraId, $sizeId) {
        return $this->getTable()->getExtraCost($extraId, $sizeId);
    }

    /**
     * get all options
     * @author Matthias Laug <laug@lieferando.de>
     * @return array
     */
    public function getOptions() {
        if (is_null($this->_options)) {
            $options = array();
            foreach ($this->getTable()->getOptions() as $opt) {
                try {
                    $row = new Yourdelivery_Model_Meal_OptionRow($opt['id']);
                    $row->setChoices($opt['choices']);
                    $options[$row->getId()] = $row;
                    foreach ($opt['items'] as $item) {
                        try {
                            $option = new Yourdelivery_Model_Meal_Option($item['oid']);
                            $options[$row->getId()]->appendOptions($option->getId(), $option);
                        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                            continue;
                        }
                    }
                } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
                    continue;
                }
            }
            $this->_options = $options;
        }
        return $this->_options;
    }

    /**
     * return the same as getOptions
     * @author Matthias Laug <laug@lieferando.de>
     * @todo: implement fast sql way ( if necessary )
     * @return array
     */
    public function getOptionsFast() {

        $optionsFast = Default_Helpers_Cache::load('optionsFast' . $this->getId());
        if (!is_null($optionsFast)) {
            return $optionsFast;
        }

        Default_Helpers_Cache::store('optionsFast' . $this->getId(), $optionsFast);
        return $optionsFast = $this->getTable()->getOptions();
    }

    /**
     * remove all options relationship with this meal
     * used to clean the data before saving new options relationships
     * @author alex
     */
    public function removeAllOptions() {
        $this->getTable()->removeAllOptions();
    }

    /**
     * get extras of meal the fast way. do not use objects here, which
     * would result in a select statement for each contructor
     * @author Matthias Laug <laug@lieferando.de>
     * @modified alex 29.09.2011
     * @modified mlaug 11.11.2011
     * @param $sizeId if set, get extras for this sitze, else - for the current size
     * @return array
     */
    public function getExtrasFast($sizeId = null) {
        $sid = is_null($sizeId) ? $this->getCurrentSize() : $sizeId;

        $extrasFast = Default_Helpers_Cache::load('extrasFast' . $this->getId() . (integer) $sid);
        if (!is_null($extrasFast)) {
            return $extrasFast;
        }

        if (!key_exists($sid, $this->_extrasFast)) {
            $this->_extrasFast[$sid] = $this->getExtras($sid);
        }

        Default_Helpers_Cache::store('extrasFast' . $this->getId() . (integer) $sid, $this->_extrasFast[$sid]);
        return $this->_extrasFast[$sid];
    }

    /**
     * get all extras sorted by group 
     * @author Allen Frank <frank@lieferando.de>
     * @since 26-06-2012
     * @param type $sizeId
     * @return array 
     */
    public function getExtras($sizeId = null) {
        $sid = is_null($sizeId) ? $this->getCurrentSize() : $sizeId;
        $extrasFromDb = $this->getTable()->getExtras($sid);
        $extras = array();        
        foreach ($extrasFromDb as $e) {
            if (!key_exists($e['groupName'], $extras)) {
                $extras[$e['groupName']]['groupName'] = $e['groupName'];
                $extras[$e['groupName']]['items'] = array();
            }            
            $extras[$e['groupName']]['items'][] = $e;
        }
       
        return $extras;
    }

    /**
     * get extras of meal for menu copy. Not using cache
     * @author alex
     * @since 22.09.2010
     * @return array
     */
    public function getExtrasForCopying() {
        $extrasGroups = array();

        foreach ($this->getTable()->getExtrasForCopying($this->getCurrentSize()) as $extra) {
            $extrasGroups[$extra['groupId']][] = $extra;
        }

        return $extrasGroups;
    }

    /**
     * remove all extras relationship with this meal for the size
     * used to clean the data before saving new extras relationships
     * @author alex
     */
    public function removeExtrasForSize($sizeId) {
        $this->getTable()->removeExtrasForSize($sizeId);
    }

    /**
     * append on extra to this meal
     * @author Matthias Laug <laug@lieferando.de>
     * @param Yourdelivery_Model_Meal_Extra $extra
     * @return boolean
     */
    public function appendExtra($extra = null) {
        if (is_null($extra) || !$extra instanceof Yourdelivery_Model_Meal_Extra) {
            return false;
        }

        if (is_null($this->_curExtras)) {
            $this->_curExtras = new SplObjectStorage();
        }

        $this->_curExtras->attach($extra);
        return true;
    }

    /**
     * append an option to this meal
     * @author Matthias Laug <laug@lieferando.de>
     * @param Yourdelivery_Model_Meal_Option $option
     * @return boolean
     */
    public function appendOption($option = null) {
        if (is_null($option) || !$option instanceof Yourdelivery_Model_Meal_Option) {
            return false;
        }

        if (is_null($this->_curOptions)) {
            $this->_curOptions = new SplObjectStorage();
        }

        $this->_curOptions->attach($option);
        return true;
    }

    /**
     * append an meailoption to this meal (half pizza)
     * @author Jens Naie <naie@lieferando.de>
     * @param Yourdelivery_Model_Meals $option
     * @return boolean
     */
    public function appendMealOption($option = null) {
        if (is_null($option) || !$option instanceof Yourdelivery_Model_Meals) {
            return false;
        }

        if (is_null($this->_curOptions)) {
            $this->_curOptions = new SplObjectStorage();
        }
        if (!$option->getCurrentSize()) {
            if(($sizeName = $this->getCurrentSizeName())) {
                $option->setCurrentSizeByName($sizeName);
            } else {
                return false;
            }
        }
        $this->_curOptions->attach($option);
        return true;
    }

    /**
     * get current extras
     * @author Matthias Laug <laug@lieferando.de>
     * @return SplObjectStorage
     */
    public function getCurrentExtras() {
        if (is_null($this->_curExtras)) {
            return new SplObjectStorage();
        }
        return $this->_curExtras;
    }

    /**
     * get current options
     * @author Matthias Laug <laug@lieferando.de>
     * @return SplObjectStorage
     */
    public function getCurrentOptions() {
        if (is_null($this->_curOptions)) {
            return new SplObjectStorage();
        }
        return $this->_curOptions;
    }

    /**
     * get current count of extras
     * @author Matthias Laug <laug@lieferando.de>
     * @return int
     */
    public function getCurrentExtrasCount() {
        if (is_null($this->_curExtras)) {
            return 0;
        }
        return $this->_curExtras->count();
    }

    /**
     * get current count of options
     * @author Matthias Laug <laug@lieferando.de>
     * @return int
     */
    public function getCurrentOptionsCount() {
        if (is_null($this->_curOptions)) {
            return 0;
        }
        return $this->_curOptions->count();
    }

    /**
     * remove a currently assigned extra
     * @author Matthias Laug <laug@lieferando.de>
     * @param Yourdelivery_Model_Meal_Extra $extra
     * @return boolean
     */
    public function removeExtra($extra = null) {
        if (is_null($extra) || !$extra instanceof Yourdelivery_Model_Meal_Extra) {
            return false;
        }

        foreach ($this->_curExtras as $e) {
            if ($e->getId() == $extra->getId()) {
                $this->_curExtras->detach($e);
                return true;
            }
        }

        return false;
    }

    /**
     * remove a currently assigned option
     * @author Matthias Laug <laug@lieferando.de>
     * @param Yourdelivery_Model_Meal_Option $option
     * @return boolean
     */
    public function removeOption($option = null) {

        if (is_null($option) || !$option instanceof Yourdelivery_Model_Meal_Option && !$option instanceof Yourdelivery_Model_Meals) {
            return false;
        }

        foreach ($this->_curOptions as $e) {
            if ($e->getId() == $option->getId() && get_class($e) == get_class($option)) {
                $this->_curOptions->detach($e);
                return true;
            }
        }

        return true;
    }

    /**
     * check if this meal is still available
     * @author Matthias Laug <laug@lieferando.de>
     * @return booelan
     */
    public function isAvailable() {
        return !$this->isDeleted();
    }

    /**
     * get service which produces item
     * @author Matthias Laug <laug@lieferando.de>
     * @return Yourdelivery_Model_ServicetypeAbstract
     */
    public function producedBy() {
        return $this->getService();
    }

    /**
     * get the tax value, but this may be overwritten
     * via setCurrentTax
     * @see setCurrentTax
     * @author Matthias Laug <laug@lieferando.de>
     * @return int
     */
    public function getMwst() {
        if (!is_null($this->getCurrentTax())) {
            return $this->getCurrentTax();
        }

        $tax = $this->_data['mwst'];
        if ($tax == 0) {
            $tax = $this->getCategoryMwst();
            if ($tax == 0) {
                $tax = 19;
            }
        }
        $this->setCurrentTax($tax);
        return $tax;
    }

    /**
     * get a readable price (from -> to)
     * used for the search menu to save space
     * @author Matthias Laug <laug@lieferando.de>
     * @return string
     */
    public function getReadablePriceInfo() {
        if (is_null($this->getId())) {
            return "";
        }
        $small = 100000;
        $big = 0;
        $small_size = "";
        $big_size = "";
        foreach ($this->getSizes() as $size) {
            $cost = intval($size['cost']);

            if ($small > $cost) {
                $small = $cost;
                $small_size = $size['name'];
            }

            if ($big < $cost) {
                $big = $cost;
                $big_size = $size['name'];
            }
        }

        if ($big == $small) {
            return intToPrice($big) . " &euro;";
        } else {
            return sprintf("von %s&euro; (%s) bis %s&euro; (%s)", intToPrice($small), $small_size, intToPrice($big), $big_size
            );
        }
    }

    /**
     * get associated table
     * @author Matthias Laug <laug@lieferando.de>
     * @return Yourdelivery_Model_DbTable_Meals
     */
    public function getTable() {
        if (is_null($this->_table)) {
            $this->_table = new Yourdelivery_Model_DbTable_Meals();
            if (!is_null($this->getId())) {
                $this->_table->setId($this->getId());
            }
        }
        return $this->_table;
    }

    /**
     * get all options row by given category
     * @since 22.09.2010
     * @author alex
     */
    public function getOptionsRowsByCategory() {
        return $this->getTable()->getOptionsRowsByCategory($this->getCategoryId());
    }

    /**
     * get all options rows having association with this meal
     * @since 22.09.2010
     * @author alex
     */
    public function getOptionsRowNNByMeal() {
        return $this->getTable()->getOptionsRowNNByMeal();
    }

    /**
     * Count of all possible Options
     * @return integer
     */
    public function getOptionsChoicesCount() {
        $count = 0;
        $options = $this->getOptions();
        foreach ($options as $option) {
            $count += $option->getChoices();
        }
        return $count;
    }

    /**
     * overwrite minamount and load mincount
     * @author Matthias Laug <laug@lieferando.de>
     * @since 26.01.2011
     * @return integer
     */
    public function getMinAmount() {
        return $this->getMincount();
    }

    /**
     * get minimum count of meal for order
     * this function returns 1 or higher
     * 
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 06.12.2010
     * @return integer
     */
    public function getMincount() {
        return (integer) $this->_data['minAmount'] < 1 ? 1 : $this->_data['minAmount'];
    }

    /**
     * get image for this meal
     *
     * if this is changed you need to change the menu wrapper as well
     * @see Yourdelivery_Model_Menu
     * 
     * @author alex
     * @since 13.12.2010
     * @return path to image or null
     */
    public function getImg($size = 'normal') {
        if ($this->getId() === null) {
            return null;
        }
        
        //check for valid input
        $valid = array('normal');
        if ( !in_array($size, $valid) ){
            $size = 'normal';
        }
        
        $width = $this->config->timthumb->meal->{$size}->width;
        $height = $this->config->timthumb->meal->{$size}->height;

        $http = isset($_SERVER["SERVER_PORT"]) && $_SERVER["SERVER_PORT"] == 443 ? 'https://' : 'http://';
        $url = sprintf('%s/%s/service/%s/categories/%s/meals/%s/%s-%d-%d.jpg', $http . $this->config->domain->timthumb, $this->config->domain->base, $this->getService()->getId(), $this->getCategory()->getId(), $this->getId(), urlencode($this->getName()), $width, $height);
        return $url;
    }

    
    /**
     * get local path of this image, stored not on S3
     * 
     * @author Alex V ait
     * @since 24.07.2012
     * @return path to image
     */
    public function getImgLocalPath() {
        $url = sprintf("%s/../storage/restaurants/%s/meals/%s/default.jpg", APPLICATION_PATH, $this->getRestaurantId(), $this->getId());
        return $url;
    }
    
    /**
     * @author Mathias Laug <laug@lieferando.de>
     * @since 12.06.2012     
     * 
     * @return boolean
     */
    public function getHasExistingPicture() {
        return (boolean) $this->_data['hasPicture'];
    }

    /**
     * Sets a new image for this meal
     * @author alex
     * @param string $name
     * @since 14.12.2010
     * @return boolean
     */
    public function setImg($name, $onlyIfNoImage = false, $backupImage = true) {

        if (is_null($this->getId())) {
            return false;
        }

        $file = "default.jpg";

        if ($onlyIfNoImage) {
            if ($this->getStorage()->exists($file)) {
                return false;
            }
        }

        $data = @file_get_contents($name);
        if ($data !== false) {
            $this->getStorage()->store($file, $data, null, $backupImage);


            //save image additionally in amazon s3
            $config = Zend_Registry::get('configuration');
            Default_Helpers_AmazonS3::putObject(
                    $config->domain->base, "restaurants/" . $this->getRestaurantId() . "/categories/" . $this->getCategoryId() . "/meals/" . $this->getId() . "/default.jpg", $name);

            $this->setHasPicture(1);
            $this->save();
            
            //clear varnish
            if ($this->config->varnish->enabled) {
                $varnishPurger = new Yourdelivery_Api_Varnish_Purger();
                $varnishPurger->addUrl($this->getImg());
                $varnishPurger->executePurge();
            }

            return true;
        }

        return false;
    }

    /**
     * get storage object of this meal
     * @author Matthias Laug <laug@liefereando.de>
     * @since 14.12.2010
     * @return Default_File_Storage
     */
    public function getStorage($path = null) {
        if (is_null($this->_storage)) {
            $this->_storage = new Default_File_Storage();
            $this->_storage->resetSubFolder();
            $this->_storage->setSubFolder('restaurants/' . $this->getRestaurantId() . '/meals/' . $this->getId());
        }
        return $this->_storage;
    }

    /**
     * get storage object of this meal
     * @author alex
     * @since 14.12.2010
     * @return bool
     */
    public function removeImg() {
        $file = "default.jpg";
        if ($this->getStorage()->exists($file)) {
            $ret = $this->getStorage()->delete($file);
            if ($ret) {
                $this->setHasPicture(0);
                $this->save();
            }
            return $ret;
        }
        return false;
    }

    /**
     * @author Matthias Laug <laug@lieferando.de>
     * @since 28.06.2011
     * @return Yourdelivery_Model_Meal_Ratings
     */
    public function getRatings() {
        $ratings = new Yourdelivery_Model_Meal_Ratings();
        $ratings->setMeal($this);
        return $ratings;
    }

    /**
     * @var array 
     */
    protected $_types = null;
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 27.06.2012
     * @return Yourdelivery_Model_Meal_Type[]
     */
    public function getTypes() {
        
        if (is_array($this->_types)) {
            return $this->_types;
        }
        
        $this->_types = array();

        $rows = $this->getTable()
                     ->getCurrent()
                     ->findDependentRowset("Yourdelivery_Model_DbTable_Meal_Types_Nn");
        
        foreach ($rows as $row) {
            $this->_types[] = new Yourdelivery_Model_Meal_Type($row->typeId);
        }

        return $this->_types;
    }

    /**
     * @author Alex Vait <vait@lieferando.de>
     * @since 11.07.2012
     * @return boolean
     */
    public function hasTypeId($typeId) {
        
        foreach ($this->getTypes() as $t) {
            if ($t->getId() == $typeId) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 27.06.2012
     * @return string
     */
    public function getTypesAsString($glue = ", ") {
        
        $names = array();
        
        $types = $this->getTypes();
        foreach ($types as $type) {
            $names[] = $type->getName();
        }
        
        return implode($glue, $names);
    }

    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 29.06.2012
     * @return string
     */
    public function getTypesHierarchyAsString($glue = ", ") {
        
        $names = array();
        
        $types = $this->getTypes();
        foreach ($types as $type) {
            $names[] = $type->getHierarchy();
        }
        
        return implode($glue, $names);
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 27.06.2012
     * @return boolean
     */
    public function addType(Yourdelivery_Model_Meal_Type $type) {
        
        if (!$this->getId() || !$type->getId()) {
            return false;
        }
        
        if ($this->hasTypeId($type->getId())) {
            return true;            
        }
        
        $nn = new Yourdelivery_Model_Meal_Type_Nn();
        $nn->setMealId($this->getId());
        $nn->setTypeId($type->getId());
        
        try {
            $this->_types[] = $type;
            return (boolean) $nn->save();
        } catch (Yourdelivery_Exception_Database_Inconsistency $e) {
        }
        
        return false;
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 27.06.2012
     * @return boolean
     */
    public function removeType(Yourdelivery_Model_Meal_Type $type) {
        
        if (!$this->getId() || !$type->getId()) {
            return false;
        }
        
        $rows = $this->getTable()
                     ->getCurrent()
                     ->findDependentRowset("Yourdelivery_Model_DbTable_Meal_Types_Nn");
        
        $n = 0;
        foreach ($rows as $row) {
            if ($row->typeId == $type->getId() ) {
                $n += $row->delete();
            }
        }
        
        $this->_types = null;
        return (boolean) $n;
    }
    
    /**
     * @author Vincent Priem <priem@lieferando.de>
     * @since 27.06.2012
     * @return boolean
     */
    public function removeTypes() {
        
        if (!$this->getId()) {
            return false;
        }
        
        $rows = $this->getTable()
                     ->getCurrent()
                     ->findDependentRowset("Yourdelivery_Model_DbTable_Meal_Types_Nn");
        
        $n = 0;
        foreach ($rows as $row) {
            $n += $row->delete();
        }
        
        $this->_types = null;
        return (boolean) $n;
    }
    
    /**
     * @var array
     */
    protected $_ingredients = null;

    /**
     * Get all ingredients of this meal as objects
     * 
     * @return array of objects
     * 
     * @author Alex Vait <vait|lieferando.de>
     * @since 27.06.2012
     */
    public function getIngredients() {
        
        if (is_array($this->_ingredients)) {
            return $this->_ingredients;
        }
        
        $this->_ingredients = array();

        $rows = $this->getTable()->getIngredients();
        foreach ($rows as $row) {
            try {
                $this->_ingredients[] = new Yourdelivery_Model_Meal_Ingredients($row['ingredientId']);
            } 
            catch (Yourdelivery_Exception_Database_Inconsistency$e) {
            }
        }

        return $this->_ingredients;
    }
    
    /**
     * @author Alex Vait <vait@lieferando.de>
     * @since 27.06.2012
     * @return string
     */
    public function getIngredientsAsString($glue = ", ") {
        
        $names = array();
        
        $ingredients = $this->getIngredients();
        foreach ($ingredients as $ingredient) {
            $names[] = $ingredient->getName();
        }
        
        asort($names);
        return implode($glue, $names);
    }
    
    /**
     * @author Alex Vait <vait@lieferando.de>
     * @since 27.06.2012
     * @return boolean
     */
    public function removeIngredients() {
        
        if (!$this->getId()) {
            return false;
        }
        
        $this->_ingredients = null;
        
        $rows = $this->getTable()
                     ->getCurrent()
                     ->findDependentRowset("Yourdelivery_Model_DbTable_Meal_Ingredients_Nn");
        
        $n = 0;
        foreach ($rows as $row) {
            $n += $row->delete();
        }
        
        return (boolean) $n;
    }    

    /**
     * @author Alex Vait <vait@lieferando.de>
     * @since 11.07.2012
     * @return boolean
     */
    public function hasIngredientId($ingredientId) {
        
        foreach ($this->getIngredients() as $i) {
            if ($i->getId() == $ingredientId) {
                return true;
            }
        }
        
        return false;
    }    
    
    /**
     * @author Alex Vait <vait@lieferando.de>
     * @since 12.07.2012
     * @param $ingredient Yourdelivery_Model_Meal_Ingredients
     * @return boolean
     */
    public function addIngredient(Yourdelivery_Model_Meal_Ingredients $ingredient) {
        
        if (!$this->getId() || !$ingredient->getId()) {
            return false;
        }
        
        if ($this->hasIngredientId($ingredient->getId())) {
            return true;            
        }
        
        try {
            $nn = new Yourdelivery_Model_Meal_Ingredients_Nn();
            $nn->setData(array(
                'mealId' => $this->getId(),
                'ingredientId' => $ingredient->getId()
            ));
            $nn->save();
            
            $this->_ingredients[] = $ingredient;
            return true;
        } 
        catch (Yourdelivery_Exception_Database_Inconsistency $e) {
        }
        
        return false;
    }   
    
    /**
     * set options/extras flag for all meal sizes relations of this meal
     * @author alex
     * @since 28.07.2011
     * @param $hasSpecials
     */
    public function setHasSpecials($hasSpecials) {
        $this->getTable()->setHasSpecials($this->getCurrentSize(), $hasSpecials);
    }

    /**
     * update hasSpecial flag for this meal
     * @author Alex Vait <vait@lieferando.de>
     * @since 15.12.2011
     * @see YD-848
     */
    public function updateHasSpecials() {
        foreach ($this->getSizes() as $s) {
            $this->setCurrentSize($s['id']);

            if ($this->hasExtras() || $this->hasOptions()) {
                $this->setHasSpecials(1);
            } else {
                $this->setHasSpecials(0);
            }
        }
    }

    /**
     * clear the meal data so that all extras and options will be corrected when another size is set
     * @author alex
     * @since 29.07.2011
     */
    protected function clearData() {
        $this->_curSizeName = null;
        $this->_curExtras = null;
        $this->_curOptions = null;
        $this->_curMealOptions = null;
        $this->_curCost = null;
        $this->_curTax = null;
        $this->_extras = array();
        $this->_extrasFast = array();
        $this->_options = null;
    }

    /**
     * get boolean if there are extras or options for that meal
     * @author Felix Haferkorn <haferkorn@lieferando.de>
     * @since 03.08.2011
     */
    public function hasSpecials() {
        $sizeId = $this->getCurrentSize();
        if (!is_null($sizeId)) {
            $relationId = Yourdelivery_Model_DbTable_Meal_SizesNn::findBySizeAndMealId($this->getId(), $sizeId);
            try {
                $relation = new Yourdelivery_Model_Meal_SizesNn($relationId);
            } catch (Yourdelivery_Exception_NoSizeSetForMeal $e) {
                return true;
            }
            return (boolean) $relation->getHasSpecials();
        }
        return true;
    }

    /**
     * get the special content
     * @author Eduardo Namba <namba@janamesa.com.br>
     * @since 20.12.2012
     */
    public function getSpecial() {
        $specialContent = $this->_data['special'];
        return $specialContent;
    }

    /**
     * get number of current size-meal association
     * @author Alex Vait <vait@lieferando.de>
     * @since 21.12.2011
     * @return string
     */
    public function getNr() {
        $curSize = $this->getCurrentSize();

        if ($curSize == 0) {
            return null;
        }

        $rel = $this->getTable()->getSizeRelation($curSize);

        return $rel['nr'];
    }

    /**
     * test if this meal or the whole category is excluded in the min. cost amount
     * @author Alex Vait <vait@lieferando.de>
     * @since 12.04.2012
     * @return boolean
     */
    public function getExcludeFromMinCost() {
        if ($this->_data['excludeFromMinCost'] == 1) {
            return true;
        }

        return $this->getCategory()->getExcludeFromMinCost();
    }
    
    /**
     * get all attributes translated
     * @author Alex Vait <vait@lieferando.de>
     * @since 27.06.2012
     * @return array
     */
    public function getAtributesAsString() {
        $attrColumn = $this->getAttributes();
        $attrAray = explode(",", $attrColumn);
        
        $attrConst = $this->getAllAttributes();
        
        $result = array();
        foreach ($attrAray as $a) {
            $result[] = $attrConst[trim($a)];
        }
                
        return implode(",", $result);
    }
    
    /**
     * @author Alex Vait  <vait@lieferando.de>
     * @since 28.06.2012
     * @return boolean
     */
    public function hasAttribute($attribute) {
        $attrColumn = $this->getAttributes();        
        $attrAray = explode(",", $attrColumn);
        
        return in_array(strtolower($attribute), $attrAray);
    }

    /**
     * get all options where this meal is an option
     * @since 17.08.2012
     * @author jens naie <naie@lieferando.de>
     * @return array
     */
    public function getMealoptionRows() {
        if (is_null($this->_mealOptionRows)) {
            $adapter = Zend_Registry::get('dbAdapterReadOnly');
            // get all options rows belonging where this meal is an option
            $sql = $adapter->select()
                    ->from(array('nn' => 'meal_mealoptions_nn'), array('nn.id'))
                    ->join(array('rows' => 'meal_options_rows'), 'nn.optionRowId = rows.id', array('rows.name'))
                    ->where($adapter->quoteInto('nn.mealId = ?', $this->getId()));
            $optRows = $adapter->fetchAll($sql);

            $this->_mealOptionRows = $optRows;
        }

        return $this->_mealOptionRows;
    }
    
    /**
     * get count of meals with specified associations set
     * $condition values: 
     *  null - get count of all undeleted online meals
     *  'ingredients' are set - if some ingredients for meals are defined (association with a table meal_ingredients_nn)
     *  'types' are set - if some ingredients for meals are defined (association with a table meal_types_nn)
     * @author Alex Vait
     * @since 28.06.2012
     * @param array $conditions
     * @return int
     */
    public static function getConditionalCount(array $conditions = null) {
        
        return Yourdelivery_Model_DbTable_Meals::getConditionalCount($conditions);
    }    
     
}
