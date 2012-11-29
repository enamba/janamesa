<?php
/**
 * @author vpriem
 * @since 13.04.2011
 */
require APPLICATION_PATH . "/../library/Smarty/plugins/modifier.escape_latex.php";
/**
 * @runTestsInSeparateProcesses
 */
class SmartyPluginsEscapeTest extends Yourdelivery_Test{

    private $_accents = "ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÑÒÓÔÕÖØÙÚÛÜÝßàáâãäåçèéêëìíîïñòóôõöøùúûüýÿ";
    private $_pl_accents = "ĆŁŚŹŻąćęłńśźż";
    private $_chars = '\\$%_}&#{´`"^~€ €bh €\\blub';

    /**
     * @author vpriem
     * @since 13.04.2011
     */
    public function testAccents(){

        $this->assertEquals(smarty_modifier_escape_latex(" " . $this->_accents . " "), '\\`{A}\\\'{A}\\^{A}\\~{A}\\"{A}{\AA}\\c{C}\\`{E}\\\'{E}\\^{E}\\"{E}\\`{I}\\\'{I}\\^{I}\\"{I}\\~{N}\\`{O}\\\'{O}\\^{O}\\~{O}\\"{O}{\O}\\`{U}\\\'{U}\\^{U}\\"{U}\\\'{Y}{\ss}\\`{a}\\\'{a}\\^{a}\\~{a}\\"{a}{\aa}\\c{c}\\`{e}\\\'{e}\\^{e}\\"{e}\\`{i}\\\'{i}\\^{i}\\"{i}\\~{n}\\`{o}\\\'{o}\\^{o}\\~{o}\\"{o}{\o}\\`{u}\\\'{u}\\^{u}\\"{u}\\\'{y}\\"{y}');
    }
    
    /**
     * @author vpriem
     * @since 13.10.2011
     */
    public function testPolishAccents(){
        
        $this->assertEquals(smarty_modifier_escape_latex(" " . $this->_pl_accents . " "), '\\\'{C}{\\L}\\\'{S}\\\'{Z}\\.Z\\k{a}\\\'{c}\\k{e}{\\l}\\\'{n}\\\'{s}\\\'{z}\\.z');
    }
    
    /**
     * @author vpriem
     * @since 13.04.2011
     */
    public function testSpecialCharacters(){
        $this->markTestSkipped('Matthias Laug <matthias.laug@gmail.com> 27.07.2012: klappt aufgrund der ph version nicht, muss geprüft werden');
        $this->assertEquals(smarty_modifier_escape_latex(' ' . $this->_chars . ' '), "\\$\\%\\_\\}\\&\\#\\{'''\\^{}\\~{}{\euro}  {\euro} bh {\euro} blub");
    }
    
}
