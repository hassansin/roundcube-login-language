<?php

/**
 * login_lang.php 
 *
 * Plugin to add a language selector in login screen
 *
 * @version 1.0
 * @author Hassansin
 * @https://github.com/hassansin
 * @example: http://kawaii.com
 */
class login_lang extends rcube_plugin
{
  
  public $task = 'login|logout';  
  public $noajax = true;  
  public $noframe = true;

  function init()
  {    
    $this->load_config();     
    $this->add_hook('logout_after',array($this,'logout_set_session'));
    $this->add_hook('template_object_loginform', array($this, 'add_login_lang'));    //program/include/rcmail_output_html.php
    $this->add_hook('login_after',array($this,'change_lang'));
  }
  public function logout_set_session($arg){
    $_SESSION['lang_selected'] = $_SESSION['language'];
    return $arg;
  }

  public function change_lang ($attr){        
    $user_lang = rcube::get_instance()->get_user_language();
    $lang = isset($_POST['_language'])? rcube_utils::get_input_value('_language', rcube_utils::INPUT_POST) : ($user_lang? $user_lang : rcube::get_instance()->config->get('language'));          
    rcube::get_instance()->load_language($lang);      
    $db = rcube::get_instance()->get_dbh();
    $db->query(
    "UPDATE ".$db->table_name('users').
    " SET language = ?".
    " WHERE user_id = ?",    
    $lang,
    $_SESSION['user_id']);
    return $attr;
  }

  public function add_login_lang($arg)
  {               
    $rcmail = rcube::get_instance();

    $list_lang = $rcmail->list_languages();
    asort($list_lang);
    

    $label = $rcmail->gettext('language');          
    $label = $rcmail->config->get('language_dropdown_label')? $rcmail->config->get('language_dropdown_label'):$label;

    $user_lang = rcube::get_instance()->get_user_language();
    $current = isset($_SESSION['lang_selected']) ? $_SESSION['lang_selected'] : $rcmail->config->get('language');              
    $current = $current? $current : $rcmail->config->get('language_dropdown_selected');
    $current = $current? $current : $user_lang;
    $current = $current? $current : 'en_US';
    $select = new html_select(array('id'=>"_language",'name'=>'_language','style'=>'width:103%;padding:3px;border-radius:-1px;box-shadow: 0 0 5px 2px rgba(71, 135, 177, 0.9);')); // make same fields as larry
    $select->add(array_values($list_lang),array_keys($list_lang));        

    
    $str  ='<tr>';
    $str .='<td class="title"><label for="_language">'.$label.'</label></td>';
    $str .='<td class="input">';
    $str .= $select->show($current);        
    $str .= '</td></tr>';

    if(preg_match('/<\/tbody>/', $arg['content'])){
      $arg['content'] = preg_replace('/<\/tbody>/', $str.'</tbody>', $arg['content']);
    }
    else{
      $arg['content'] = $arg['content'].$str;
    }

    // use exitings id's message and bottomline    
    //$rcmail->output->add_footer( $str );        
    return $arg;
  }
}

?>
