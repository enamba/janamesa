<?php
/**
 * Description of PictureTest
 *
 * @author Alex Vait <vait@lieferando.de>
 */
/**
 * @runTestsInSeparateProcesses 
 */
class Administration_Service_Category_PictureTest extends Yourdelivery_Test {

    protected static $admin = null;
    protected static $db;

    public function setUp() {
        parent::setUp();
        self::$db = Zend_Registry::get('dbAdapter');
        $session = new Zend_Session_Namespace('Administration');
        $session->admin = $this->createRandomAdministrationUser();
        self::$admin = $session->admin;
        $this->getRequest()->setHeader('Authorization', 'Basic '.  base64_encode('gf:thisishell'));
    }

    /**
     * @author Alex Vait <vait@lieferando.de>
     * @since 02.12.2011
     */
    public function testCreate() {
        $name = "CategoryPicture" . Default_Helper::generateRandomString(4);
        $description = Default_Helper::generateRandomString(10);

        $post = array('name' => $name, 'description' => $description);

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost($post);

        $this->dispatch('/administration_service_category_picture/create/');

        $picCategoryId = (integer) self::$db->fetchOne('SELECT MAX(`id`) FROM `category_picture`');

        $this->assertRedirect('/administration_service_category_picture/edit/id/' . $picCategoryId);


        $picCategory = new Yourdelivery_Model_Category_Picture($picCategoryId);

        $this->assertFalse(is_null($picCategory));
        $this->assertNotEquals($picCategory->getId(), 0);
        $this->assertEquals($picCategory->getName(), $name);
        $this->assertEquals($picCategory->getDescription(), $description);
    }

    /**
     * @author Alex Vait <vait@lieferando.de>
     * @since 02.12.2011
     */
    public function testEdit() {
        $cat = $this->getRandomPictureCategory();

        $name = "CategoryPicture" . Default_Helper::generateRandomString(4);
        $description = Default_Helper::generateRandomString(10);

        $post = array('name' => $name, 'description' => $description, 'updatecat' => 'speichern');

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost($post);

        $this->dispatch('/administration_service_category_picture/edit/id/' . $cat->getId());

        $picCategoryId = $cat->getId();

        $this->assertRedirect('/administration_service_category_picture/edit/id/' . $picCategoryId);


        $picCategory = new Yourdelivery_Model_Category_Picture($picCategoryId);

        $this->assertFalse(is_null($picCategory));
        $this->assertNotEquals($picCategory->getId(), 0);
        $this->assertEquals($picCategory->getName(), $name);
        $this->assertEquals($picCategory->getDescription(), $description);
    }

    /**
     * @author Alex Vait <vait@lieferando.de>
     * @since 02.12.2011
     */
    public function testAssign() {
        $s = $this->getRandomService();

        $mealCategoryToPicture = array();
        foreach ($s->getMealCategories() as $cat) {
            $picCategory = $this->getRandomPictureCategory();
            $mealCategoryToPicture[$cat->getId()] = $picCategory->getId();
        }

        $post = array('pcat' => $mealCategoryToPicture, 'submitassign' => 'Speichern');

        $request = $this->getRequest();
        $request->setMethod('POST');
        $request->setPost($post);

        $this->dispatch('/administration_service_category_picture/assign/id/' . $s->getId());

        $this->assertRedirect('/administration_service_edit/piccategories/id/' . $s->getId());

        foreach ($s->getMealCategories() as $cat) {
            $this->assertEquals($cat->getCategoryPictureId(), $mealCategoryToPicture[$cat->getId()][0]);
        }
    }

}

?>
