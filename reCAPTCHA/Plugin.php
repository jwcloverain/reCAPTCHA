<?php
/**
 * reCAPTCHA验证码插件
 * 
 * @package reCAPTCHA
 * @author 啸傲居士
 * @version 0.0.1
 * @link http://geaya.com
 */

require_once('lib/vendor/autoload.php');

class reCAPTCHA_Plugin implements Typecho_Plugin_Interface
{

    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     * 
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate() {
		Typecho_Plugin::factory('Widget_Feedback')->comment = array(__CLASS__, 'filter');
    }
    
    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     * 
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate() {}
    
    /**
     * 个人用户的配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form) {}
    
	/**
     * 获取插件配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
	public static function config(Typecho_Widget_Helper_Form $form) {
		$siteKeyDescription = _t("To use reCAPTCHA you must get an API key from <a href='https://www.google.com/recaptcha/admin/create'>https://www.google.com/recaptcha/admin/create</a>");
		$siteKey = new Typecho_Widget_Helper_Form_Element_Text('siteKey', NULL, '', _t('Site Key:'), $siteKeyDescription);
		$secretKey = new Typecho_Widget_Helper_Form_Element_Text('secretKey', NULL, '', _t('Serect Key:'), _t(''));
		
		$form->addInput($siteKey);
		$form->addInput($secretKey);
	}
	
	/**
	 * 展示验证码
	 */
        public static function output() {
                $siteKey = Typecho_Widget::widget('Widget_Options')->plugin('reCAPTCHA')->siteKey;
                if (isset($siteKey)) {
                        echo '<script src="https://recaptcha.net/recaptcha/api.js" async defer></script>
                                <div class="g-recaptcha" data-sitekey=' . $siteKey . '></div>';
                }
                else { return; }
        }
  
	public static function filter($comments, $obj) {
	if (isset($_POST['g-recaptcha-response'])) {
		$siteKey = Typecho_Widget::widget('Widget_Options')->plugin('reCAPTCHA')->siteKey;
		$secretKey = Typecho_Widget::widget('Widget_Options')->plugin('reCAPTCHA')->secretKey;
		$userObj = $obj->widget('Widget_User');
		
		if($userObj->hasLogin() && $userObj->pass('administrator', true)) {
			return $comments;
		}
	
		$recaptcha = new \ReCaptcha\ReCaptcha($secretKey);
    		// static $realip;
    		// if(isset($_SERVER)){
        	//	if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
            	//		$realip=$_SERVER['HTTP_X_FORWARDED_FOR'];
        	//	}else if(isset($_SERVER['HTTP_CLIENT_IP'])){
            	//		$realip=$_SERVER['HTTP_CLIENT_IP'];
        	//	}else{
            	//		$realip=$_SERVER['REMOTE_ADDR'];
        	//	}
    		// }else{
        	//	if(getenv('HTTP_X_FORWARDED_FOR')){
            	//		$realip=getenv('HTTP_X_FORWARDED_FOR');
        	//	}else if(getenv('HTTP_CLIENT_IP')){
            	//		$realip=getenv('HTTP_CLIENT_IP');
        	//	}else{
            	//		$realip=getenv('REMOTE_ADDR');
        	//	}
    		// }
		// return $realip;
		
		$ip = $_SERVER['REMOTE_ADDR'];	
		$resp = $recaptcha->verify($_POST['g-recaptcha-response'], $realip);

		if (!$resp->isSuccess()) {
			// What happens when the CAPTCHA was entered incorrectly
			// die ("The reCAPTCHA wasn't entered correctly. Go back and try it again." .
			// 	"(reCAPTCHA said: " . $resp->error . ")");
			throw new Typecho_Widget_Exception(_t('验证码不正确哦！'));
		}
		else {return $comments;}
		}
	}
        
}
