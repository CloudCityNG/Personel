<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This is built using the Clean template to allow for new theme's using
 * Moodle's new Bootstrap theme engine
 *
 *
 * @package   theme_co
 * @copyright 2013 Julian Ridden
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$settings = null;

defined('MOODLE_INTERNAL') || die;


	$ADMIN->add('themes', new admin_category('theme_colms', 'CoLMS'));

	// "geneicsettings" settingpage
	$temp = new admin_settingpage('theme_colms_generic', 'General Settings');
	
	// Default Site icon setting.
    $name = 'theme_colms/siteicon';
    $title = get_string('siteicon', 'theme_colms');
    $description = get_string('siteicondesc', 'theme_colms');
    $default = 'laptop';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $temp->add($setting);
	
    // Before login Logo file setting.
    $name = 'theme_colms/bllogo';
    $title = get_string('bllogo', 'theme_colms');
    $description = get_string('bllogodesc', 'theme_colms');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'bllogo');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // After login Logo file setting.
    $name = 'theme_colms/allogo';
    $title = get_string('allogo', 'theme_colms');
    $description = get_string('allogodesc', 'theme_colms');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'allogo');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    //$name = 'theme_co/numberofdashboards';
    //$title = get_string('numberofdashboards' , 'theme_co');
    //$description = get_string('numberofdashboardsdesc', 'theme_co');
    //$default = '0';
    //$choices = array('0'=>'2','1'=>'4','2'=>'6');
    //$setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    //$setting->set_updatedcallback('theme_reset_all_caches');
    //$temp->add($setting);
    
    
    // Main theme background colour setting.
    $name = 'theme_colms/themecolor';
    $title = get_string('themecolor', 'theme_colms');
    $description = get_string('themecolordesc', 'theme_colms');
    $default = '#30add1';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Main theme Hover colour setting.
    $name = 'theme_colms/themehovercolor';
    $title = get_string('themehovercolor', 'theme_colms');
    $description = get_string('themehovercolordesc', 'theme_colms');
    $default = '#29a1c4';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Copyright setting.
    $name = 'theme_colms/copyright';
    $title = get_string('copyright', 'theme_colms');
    $description = get_string('copyrightdesc', 'theme_colms');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $temp->add($setting);
    
    // Footnote setting.
    $name = 'theme_colms/footnote';
    $title = get_string('footnote', 'theme_colms');
    $description = get_string('footnotedesc', 'theme_colms');
    $default = '';
    $setting = new admin_setting_confightmleditor($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Custom CSS file.
    $name = 'theme_colms/customcss';
    $title = get_string('customcss', 'theme_colms');
    $description = get_string('customcssdesc', 'theme_colms');
    $default = '';
    $setting = new admin_setting_configtextarea($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

 	$ADMIN->add('theme_colms', $temp);
 
 
    /* Slideshow Widget Settings */
    $temp = new admin_settingpage('theme_colms_slideshow', get_string('slideshowheading', 'theme_colms'));
    $temp->add(new admin_setting_heading('theme_colms_slideshow', get_string('slideshowheadingsub', 'theme_colms'),
            format_text(get_string('slideshowdesc' , 'theme_colms'), FORMAT_MARKDOWN)));
    
        // Hide slideshow on phones.
    $name = 'theme_colms/charts';
    $title = get_string('charts' , 'theme_colms');
    $description = get_string('chartsdesc', 'theme_colms');
    $display = get_string('display', 'theme_colms');
    $dontdisplay = get_string('dontdisplay', 'theme_colms');
    $default = 'display';
    $choices = array(''=>$display, 'charts'=>$dontdisplay);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Hide slideshow on phones.
    $name = 'theme_colms/hideonphone';
    $title = get_string('hideonphone' , 'theme_colms');
    $description = get_string('hideonphonedesc', 'theme_colms');
    $display = get_string('display', 'theme_colms');
    $dontdisplay = get_string('dontdisplay', 'theme_colms');
    $default = 'display';
    $choices = array(''=>$display, 'hidden-phone'=>$dontdisplay);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    /*
     * Slide 1
     */

    // Title.
    $name = 'theme_colms/slide1';
    $title = get_string('slide1', 'theme_colms');
    $description = get_string('slide1desc', 'theme_colms');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $default = '';
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Image.
    $name = 'theme_colms/slide1image';
    $title = get_string('slide1image', 'theme_colms');
    $description = get_string('slide1imagedesc', 'theme_colms');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'slide1image');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Caption.
    $name = 'theme_colms/slide1caption';
    $title = get_string('slide1caption', 'theme_colms');
    $description = get_string('slide1captiondesc', 'theme_colms');
    $setting = new admin_setting_configtextarea($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // URL.
    $name = 'theme_colms/slide1url';
    $title = get_string('slide1url', 'theme_colms');
    $description = get_string('slide1urldesc', 'theme_colms');
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    /*
     * Slide 2
     */

    // Title.
    $name = 'theme_colms/slide2';
    $title = get_string('slide2', 'theme_colms');
    $description = get_string('slide2desc', 'theme_colms');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $default = '';
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Image.
    $name = 'theme_colms/slide2image';
    $title = get_string('slide2image', 'theme_colms');
    $description = get_string('slide2imagedesc', 'theme_colms');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'slide2image');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Caption.
    $name = 'theme_colms/slide2caption';
    $title = get_string('slide2caption', 'theme_colms');
    $description = get_string('slide2captiondesc', 'theme_colms');
    $setting = new admin_setting_configtextarea($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // URL.
    $name = 'theme_colms/slide2url';
    $title = get_string('slide2url', 'theme_colms');
    $description = get_string('slide2urldesc', 'theme_colms');
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    /*
     * Slide 3
     */

    // Title.
    $name = 'theme_colms/slide3';
    $title = get_string('slide3', 'theme_colms');
    $description = get_string('slide3desc', 'theme_colms');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $default = '';
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Image.
    $name = 'theme_colms/slide3image';
    $title = get_string('slide3image', 'theme_colms');
    $description = get_string('slide3imagedesc', 'theme_colms');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'slide3image');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Caption.
    $name = 'theme_colms/slide3caption';
    $title = get_string('slide3caption', 'theme_colms');
    $description = get_string('slide3captiondesc', 'theme_colms');
    $setting = new admin_setting_configtextarea($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // URL.
    $name = 'theme_colms/slide3url';
    $title = get_string('slide3url', 'theme_colms');
    $description = get_string('slide3urldesc', 'theme_colms');
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    /*
     * Slide 4
     */

    // Title.
    $name = 'theme_colms/slide4';
    $title = get_string('slide4', 'theme_colms');
    $description = get_string('slide4desc', 'theme_colms');
    $setting = new admin_setting_configtext($name, $title, $description, '');
    $default = '';
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Image.
    $name = 'theme_colms/slide4image';
    $title = get_string('slide4image', 'theme_colms');
    $description = get_string('slide4imagedesc', 'theme_colms');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'slide4image');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Caption.
    $name = 'theme_colms/slide4caption';
    $title = get_string('slide4caption', 'theme_colms');
    $description = get_string('slide4captiondesc', 'theme_colms');
    $setting = new admin_setting_configtextarea($name, $title, $description, '');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // URL.
    $name = 'theme_colms/slide4url';
    $title = get_string('slide4url', 'theme_colms');
    $description = get_string('slide4urldesc', 'theme_colms');
    $setting = new admin_setting_configtext($name, $title, $description, '', PARAM_URL);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    
    $ADMIN->add('theme_colms', $temp);
    

	/* Marketing Spot Settings */
	$temp = new admin_settingpage('theme_colms_marketing', get_string('marketingheading', 'theme_colms'));
	$temp->add(new admin_setting_heading('theme_colms_marketing', get_string('marketingheadingsub', 'theme_colms'),
            format_text(get_string('marketingdesc' , 'theme_colms'), FORMAT_MARKDOWN)));
	
	// Toggle Marketing Spots.
    $name = 'theme_colms/togglemarketing';
    $title = get_string('togglemarketing' , 'theme_colms');
    $description = get_string('togglemarketingdesc', 'theme_colms');
    $display = get_string('display', 'theme_colms');
    $dontdisplay = get_string('dontdisplay', 'theme_colms');
    $default = 'display';
    $choices = array('1'=>$display, '0'=>$dontdisplay);
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
	
	//Marketing Spot One.

	$name = 'theme_colms/marketing1';
    $title = get_string('marketing1', 'theme_colms');
    $description = get_string('marketing1desc', 'theme_colms');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

 // Image.
    $name = 'theme_colms/marketing1content';
    $title = get_string('marketing1content', 'theme_colms');
    $description = get_string('marketing1contentdesc', 'theme_colms');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'marketing1content');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Marketing one link setting.
    $name = 'theme_colms/marketing1link';
    $title = get_string('marketing1link', 'theme_colms');
    $description = get_string('marketing1linkdesc', 'theme_colms');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    //Marketing Spot Two.
    $name = 'theme_colms/marketing2';
    $title = get_string('marketing2', 'theme_colms');
    $description = get_string('marketing2desc', 'theme_colms');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

 // Image.
    $name = 'theme_colms/marketing2content';
    $title = get_string('marketing2content', 'theme_colms');
    $description = get_string('marketing2contentdesc', 'theme_colms');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'marketing2content');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
        // Marketing two link setting.
    $name = 'theme_colms/marketing2link';
    $title = get_string('marketing2link', 'theme_colms');
    $description = get_string('marketing2linkdesc', 'theme_colms');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    //Marketing Spot Three.
	$name = 'theme_colms/marketing3';
    $title = get_string('marketing3', 'theme_colms');
    $description = get_string('marketing3desc', 'theme_colms');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

 // Image.
    $name = 'theme_colms/marketing3content';
    $title = get_string('marketing3content', 'theme_colms');
    $description = get_string('marketing3contentdesc', 'theme_colms');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'marketing3content');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
   // Marketing three link setting.
    $name = 'theme_colms/marketing3link';
    $title = get_string('marketing3link', 'theme_colms');
    $description = get_string('marketing3linkdesc', 'theme_colms');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
        //Marketing Spot Four.
	$name = 'theme_colms/marketing4';
    $title = get_string('marketing4', 'theme_colms');
    $description = get_string('marketing4desc', 'theme_colms');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

 // Image.
    $name = 'theme_colms/marketing4content';
    $title = get_string('marketing4content', 'theme_colms');
    $description = get_string('marketing4contentdesc', 'theme_colms');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'marketing4content');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
       // Marketing four link setting.
    $name = 'theme_colms/marketing4link';
    $title = get_string('marketing4link', 'theme_colms');
    $description = get_string('marketing4linkdesc', 'theme_colms');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    $name = 'theme_colms/video';
    $title = get_string('video', 'theme_colms');
    $description = get_string('videodesc', 'theme_colms');
    $setting = new admin_setting_configstoredfile($name, $title, $description, 'video');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    $ADMIN->add('theme_colms', $temp);

	
	/* Social Network Settings */
	$temp = new admin_settingpage('theme_colms_social', get_string('socialheading', 'theme_colms'));
	$temp->add(new admin_setting_heading('theme_colms_social', get_string('socialheadingsub', 'theme_colms'),
            format_text(get_string('socialdesc' , 'theme_colms'), FORMAT_MARKDOWN)));
	
    // Facebook url setting.
    $name = 'theme_colms/facebook';
    $title = get_string('facebook', 'theme_colms');
    $description = get_string('facebookdesc', 'theme_colms');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Twitter url setting.
    $name = 'theme_colms/twitter';
    $title = get_string('twitter', 'theme_colms');
    $description = get_string('twitterdesc', 'theme_colms');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // Google+ url setting.
    $name = 'theme_colms/googleplus';
    $title = get_string('googleplus', 'theme_colms');
    $description = get_string('googleplusdesc', 'theme_colms');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);

    // LinkedIn url setting.
    $name = 'theme_colms/linkedin';
    $title = get_string('linkedin', 'theme_colms');
    $description = get_string('linkedindesc', 'theme_colms');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // YouTube url setting.
    $name = 'theme_colms/youtube';
    $title = get_string('youtube', 'theme_colms');
    $description = get_string('youtubedesc', 'theme_colms');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    
    // Flickr url setting.
    $name = 'theme_colms/flickr';
    $title = get_string('flickr', 'theme_colms');
    $description = get_string('flickrdesc', 'theme_colms');
    $default = '';
    $setting = new admin_setting_configtext($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $temp->add($setting);
    $ADMIN->add('theme_colms', $temp);