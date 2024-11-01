<?php
/*
    "WordPress Plugin Template" Copyright (C) 2020 Michael Simpson  (email : michael.d.simpson@gmail.com)

    This file is part of WordPress Plugin Template for WordPress.

    WordPress Plugin Template is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    WordPress Plugin Template is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
 
    You should have received a copy of the GNU General Public License
    along with Contact Form to Database Extension.
    If not, see http://www.gnu.org/licenses/gpl-3.0.html
*/

class WooreviewsImporter_OptionsManager
{

    public function getOptionNamePrefix()
    {
        return get_class($this) . '_';
    }


    /**
     * Define your options meta data here as an array, where each element in the array
     * @return array of key=>display-name and/or key=>array(display-name, choice1, choice2, ...)
     * key: an option name for the key (this name will be given a prefix when stored in
     * the database to ensure it does not conflict with other plugin options)
     * value: can be one of two things:
     *   (1) string display name for displaying the name of the option to the user on a web page
     *   (2) array where the first element is a display name (as above) and the rest of
     *       the elements are choices of values that the user can select
     * e.g.
     * array(
     *   'item' => 'Item:',             // key => display-name
     *   'rating' => array(             // key => array ( display-name, choice1, choice2, ...)
     *       'CanDoOperationX' => array('Can do Operation X', 'Administrator', 'Editor', 'Author', 'Contributor', 'Subscriber'),
     *       'Rating:', 'Excellent', 'Good', 'Fair', 'Poor')
     */
    public function getOptionMetaData()
    {
        return array();
    }

    /**
     * @return array of string name of options
     */
    public function getOptionNames()
    {
        return array_keys($this->getOptionMetaData());
    }

    /**
     * Override this method to initialize options to default values and save to the database with add_option
     * @return void
     */
    protected function initOptions()
    { }

    /**
     * Cleanup: remove all options from the DB
     * @return void
     */
    protected function deleteSavedOptions()
    {
        $optionMetaData = $this->getOptionMetaData();
        if (is_array($optionMetaData)) {
            foreach ($optionMetaData as $aOptionKey => $aOptionMeta) {
                $prefixedOptionName = $this->prefix($aOptionKey); // how it is stored in DB
                delete_option($prefixedOptionName);
            }
        }
    }

    /**
     * @return string display name of the plugin to show as a name/title in HTML.
     * Just returns the class name. Override this method to return something more readable
     */
    public function getPluginDisplayName()
    {
        return get_class($this);
    }

    /**
     * Get the prefixed version input $name suitable for storing in WP options
     * Idempotent: if $optionName is already prefixed, it is not prefixed again, it is returned without change
     * @param  $name string option name to prefix. Defined in settings.php and set as keys of $this->optionMetaData
     * @return string
     */
    public function prefix($name)
    {
        $optionNamePrefix = $this->getOptionNamePrefix();
        if (strpos($name, $optionNamePrefix) === 0) { // 0 but not false
            return $name; // already prefixed
        }
        return $optionNamePrefix . $name;
    }

    /**
     * Remove the prefix from the input $name.
     * Idempotent: If no prefix found, just returns what was input.
     * @param  $name string
     * @return string $optionName without the prefix.
     */
    public function &unPrefix($name)
    {
        $optionNamePrefix = $this->getOptionNamePrefix();
        if (strpos($name, $optionNamePrefix) === 0) {
            return substr($name, strlen($optionNamePrefix));
        }
        return $name;
    }

    /**
     * A wrapper function delegating to WP get_option() but it prefixes the input $optionName
     * to enforce "scoping" the options in the WP options table thereby avoiding name conflicts
     * @param $optionName string defined in settings.php and set as keys of $this->optionMetaData
     * @param $default string default value to return if the option is not set
     * @return string the value from delegated call to get_option(), or optional default value
     * if option is not set.
     */
    public function getOption($optionName, $default = null)
    {
        $prefixedOptionName = $this->prefix($optionName); // how it is stored in DB
        $retVal = get_option($prefixedOptionName);
        if (!$retVal && $default) {
            $retVal = $default;
        }
        return $retVal;
    }

    /**
     * A wrapper function delegating to WP delete_option() but it prefixes the input $optionName
     * to enforce "scoping" the options in the WP options table thereby avoiding name conflicts
     * @param  $optionName string defined in settings.php and set as keys of $this->optionMetaData
     * @return bool from delegated call to delete_option()
     */
    public function deleteOption($optionName)
    {
        $prefixedOptionName = $this->prefix($optionName); // how it is stored in DB
        return delete_option($prefixedOptionName);
    }

    /**
     * A wrapper function delegating to WP add_option() but it prefixes the input $optionName
     * to enforce "scoping" the options in the WP options table thereby avoiding name conflicts
     * @param  $optionName string defined in settings.php and set as keys of $this->optionMetaData
     * @param  $value mixed the new value
     * @return null from delegated call to delete_option()
     */
    public function addOption($optionName, $value)
    {
        $prefixedOptionName = $this->prefix($optionName); // how it is stored in DB
        return add_option($prefixedOptionName, $value);
    }

    /**
     * A wrapper function delegating to WP add_option() but it prefixes the input $optionName
     * to enforce "scoping" the options in the WP options table thereby avoiding name conflicts
     * @param  $optionName string defined in settings.php and set as keys of $this->optionMetaData
     * @param  $value mixed the new value
     * @return null from delegated call to delete_option()
     */
    public function updateOption($optionName, $value)
    {
        $prefixedOptionName = $this->prefix($optionName); // how it is stored in DB
        return update_option($prefixedOptionName, $value);
    }

    /**
     * A Role Option is an option defined in getOptionMetaData() as a choice of WP standard roles, e.g.
     * 'CanDoOperationX' => array('Can do Operation X', 'Administrator', 'Editor', 'Author', 'Contributor', 'Subscriber')
     * The idea is use an option to indicate what role level a user must minimally have in order to do some operation.
     * So if a Role Option 'CanDoOperationX' is set to 'Editor' then users which role 'Editor' or above should be
     * able to do Operation X.
     * Also see: canUserDoRoleOption()
     * @param  $optionName
     * @return string role name
     */
    public function getRoleOption($optionName)
    {
        $roleAllowed = $this->getOption($optionName);
        if (!$roleAllowed || $roleAllowed == '') {
            $roleAllowed = 'Administrator';
        }
        return $roleAllowed;
    }

    /**
     * Given a WP role name, return a WP capability which only that role and roles above it have
     * http://codex.wordpress.org/Roles_and_Capabilities
     * @param  $roleName
     * @return string a WP capability or '' if unknown input role
     */
    protected function roleToCapability($roleName)
    {
        switch ($roleName) {
            case 'Super Admin':
                return 'manage_options';
            case 'Administrator':
                return 'manage_options';
            case 'Editor':
                return 'publish_pages';
            case 'Author':
                return 'publish_posts';
            case 'Contributor':
                return 'edit_posts';
            case 'Subscriber':
                return 'read';
            case 'Anyone':
                return 'read';
        }
        return '';
    }

    /**
     * @param $roleName string a standard WP role name like 'Administrator'
     * @return bool
     */
    public function isUserRoleEqualOrBetterThan($roleName)
    {
        if ('Anyone' == $roleName) {
            return true;
        }
        $capability = $this->roleToCapability($roleName);
        return current_user_can($capability);
    }

    /**
     * @param  $optionName string name of a Role option (see comments in getRoleOption())
     * @return bool indicates if the user has adequate permissions
     */
    public function canUserDoRoleOption($optionName)
    {
        $roleAllowed = $this->getRoleOption($optionName);
        if ('Anyone' == $roleAllowed) {
            return true;
        }
        return $this->isUserRoleEqualOrBetterThan($roleAllowed);
    }

    /**
     * see: http://codex.wordpress.org/Creating_Options_Pages
     * @return void
     */
    public function createSettingsMenu()
    {
        $pluginName = $this->getPluginDisplayName();
        //create new top-level menu
        add_menu_page(
            $pluginName . ' Plugin Settings',
            $pluginName,
            'administrator',
            get_class($this),
            array(&$this, 'settingsPage')
            // plugins_url('/images/icon.jpg', __FILE__)
        ); // if you call 'plugins_url; be sure to "require_once" it

        //call register settings function
        add_action('admin_init', array(&$this, 'registerSettings'));
    }

    public function registerSettings()
    {
        $settingsGroup = get_class($this) . '-settings-group';
        $optionMetaData = $this->getOptionMetaData();
        foreach ($optionMetaData as $aOptionKey => $aOptionMeta) {
            register_setting($settingsGroup, $aOptionMeta);
        }
    }



    /**
     * Creates HTML for the Administration page to set options for this plugin.
     * Override this method to create a customized page.
     * @return void
     */
    public function settingsPage()
    {



        // add_action('wp_enqueue_scripts', 'jquery_add_to_contact');
        // wp_enqueue_script('underscore', plugin_dir_url(__FILE__) . 'js/underscore.js', array('jquery'), NULL, false);
        wp_enqueue_style('mdb', plugin_dir_url(__FILE__) . 'css/mdb.min.css');
        wp_enqueue_script('bootstrap', plugin_dir_url(__FILE__) . 'js/bootstrap.min.js', array('jquery'), NULL, false);
        wp_enqueue_script('toast', plugin_dir_url(__FILE__) . 'js/jquery.toast.min.js', array('jquery'), NULL, false);
        wp_enqueue_style('bootstrapCss', plugin_dir_url(__FILE__) . 'css/bootstrap_1.min.css');
        wp_enqueue_style('toastCss', plugin_dir_url(__FILE__) . 'css/jquery.toast.min.css');
        wp_enqueue_style('custom', plugin_dir_url(__FILE__) . 'css/main.css');
        wp_enqueue_script('reviews', plugin_dir_url(__FILE__) . 'js/reviews.js', array('jquery'), NULL, false);


        wp_localize_script(
            'reviews',
            'wooshark_params_reviews',
            array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('ajax-nonce')
            )
        );





        ?>
            <script src="https://kit.fontawesome.com/45abdd2158.js" crossorigin="anonymous"></script>



            <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist" style="padding-top:5px;margin-top:8px;  background-color:#9aa462; border-top-left-radius:10px; border-top-right-radius:14px">
                <!-- <li class="nav-item active">
                    <a class="nav-link active" style="margin-left: 5px" id="pills-home-tab" data-toggle="pill" href="#pills-home" role="tab" aria-controls="pills-home" aria-selected="true">AliExpress import</a>
                </li> -->
                <!-- <li class="nav-item">
                    <a class="nav-link" id="pills-connect-tab" data-toggle="pill" href="#pills-connect" role="tab" aria-controls="pills-connect" aria-selected="false">Connect to store</a>
                </li> -->
                <li class="nav-item active">
                    <a class="nav-link active" id="pills-connect-products" data-toggle="pill" href="#pills-products" role="tab" aria-controls="pills-connect" aria-selected="false">Products <i class="fa fa-refresh"> </i> </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="how-it-works-tab" data-toggle="pill" href="#how-it-works" role="tab" aria-controls="how-it-works" aria-selected="false">How it works</a>
                </li>
                <li class="nav-item ">
                    <a class="nav-link " id="pills-config-tab" data-toggle="pill" href="#pills-config" role="tab" aria-controls="pills-config" aria-selected="false">Configuration <i class="fa fa-cogs"> </i></a>
                </li>
                <!-- <li class="nav-item">
                    <a class="nav-link" id="revLibrary" data-toggle="pill" href="#reviews-library" role="tab" aria-controls="reviews-library" aria-selected="false"> Reviews library </a>
                </li> -->
                <li class="nav-item">
                    <a class="nav-link" id="go-pro-tab" data-toggle="pill" href="#go-pro" role="tab" aria-controls="go-pro" aria-selected="false">Go Pro</a>
                </li>


             

                <li class="nav-item">
                    <a class="nav-link" id="support-tab" data-toggle="pill" href="#support" role="tab" aria-controls="support" aria-selected="false">Common Issues</a>
                </li>

               

                <!-- <li class="nav-item">
                    <a class="nav-link" id="pills-orders-tab" data-toggle="pill" href="#pills-orders" role="tab" aria-controls="pills-orders" aria-selected="false"> Order placement</a>
                </li> -->


                <!-- <li class="nav-item">
                    <a class="nav-link" id="pills-activation-tab" data-toggle="pill" href="#pills-activation" role="tab" aria-controls="pills-activation" aria-selected="false">Activation <i class="fa fa-refresh"> </i></a>
                </li> -->

                <!-- <li class="nav-item">
                    <a class="nav-link" id="pills-advanced-tab" data-toggle="pill" href="#pills-advanced" role="tab" aria-controls="pills-advanced" aria-selected="false">Pro features</a>
                </li> -->






            </ul>





            <!-- ///////////////////////////////////////////// -->
            <div class="tab-content" id="pills-tabContent" style="background-color:#f3f5f6">
                <div style="height:20px; color:grey"></div>







                <div class="tab-pane fade" id="pills-config" role="tabpanel" aria-labelledby="pills-config-tab" style="background-color:#f3f5f6">
                    <div class="global-configuration-section">

                        <div style="height:20px; color:grey"></div>


                        <!--  -->
                        <!--  FORMULA -->
                        <!--  -->
                        <!--  -->
                        <!--  -->
                        <!--  -->
                        <!--  -->
                        <!--  -->


                        <div style="border-radius:10px;  padding:7px; margin: 20px;  color:red; font-family: monospace; box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;">
                                        Faster import, Directly from Amazon and aliExpress website ?
                                        <button class="button-5">
                            <a style="color:black" href="https://chrome.google.com/webstore/detail/amazon-and-aliexpress-rev/folccdmdpbpllglnhokbihjedgedjabb" target="_blank" >
                            Install the chrome extension from here
                            </a>
                        </button>
                    </div>

                    


                        <div class="switch-text" style="margin-left:2%; margin-right:2%; margin-top:2%; padding:2%; border-radius:10px; background-color:white">

                            <h4 style="font-weight:bold">
                                Common configuration
                            </h4>

                            <!-- <div style="margin-bottom:15px; margin-top:20px; display:flex" >
                                <span style='flex: 1 1 40%; color:#899195; font-size:'><input id="isChangeReviewsStars" type="checkbox" /> Change reviews stars to custom symbol</span>
                                <input style="height:30px" type="color" id="color-stars" name="color" value="#e66465">
                                <input style="margin-left:15px; flex: 1 1 50%" type="text"  id="review-icon" placeholder="fa fa-heart; paste here the icon you want to use to replace the stars of each review">
                            </div> -->

                            <div style="margin-bottom:15px; margin-top:20px">
                                <span style='color:#899195; font-size:'><input id="isHideReviewsDisplay" type="checkbox" /> Display reviews below the product description instead of separate tab</span>
                            </div>

                            <div style="margin-bottom:15px; margin-top:20px">
                                <span style='color:#899195; font-size:'><input id="isEnableReviewsTag" type="checkbox" /> Enable adding reviews tags</span>
                            </div>



                            <!-- <div style="display:flex"> -->
                            <!-- <div style="flex:1 1 44%; margin-right:1%">
                                    <label style="margin-bottom:10px; color:#899195">Text to be replaced</label>
                                    <input id="textToBeReplaced" style="margin-bottom:10px" placeholder="text to replece" class="form  form-control" />
                                </div>
                                <div style="flex:1 1 44%;  margin-left:1%">
                                    <label style="margin-bottom:10px; color:#899195">New text</label>
                                    <input id="textToReplace" style="margin-bottom:10px" placeholder="text to replece" class="form  form-control" />
                                </div> -->


                            <!-- </div> -->
                        </div>






                        <!--  -->
                        <!--  FIRST -->
                        <!--  -->
                        <!--  -->
                        <!--  -->
                        <!--  -->
                        <!--  -->
                        <!--  -->


                        <div class="first-level-section" style="display:flex;">
                            <div class="update-product-configuration-section" style="flex: 1 1 48%; margin-left:2%; margin-right:1%; margin-top:2%;; padding:2%; border-radius:10px; background-color:white">

                                <h4 style="font-weight:bold">
                                    AliExpress reviews configuration
                                </h4>


                                <div style="margin-bottom:15px; margin-top:20px">
                                    <span style='color:#899195; font-size:'><input id="importOnlyWithImagesAliexpress" id="group1" type="checkbox" /> Import only reviews including images</span>
                                </div>

                                <div style="margin-bottom:15px">
                                    <span style='color:#899195; font-size:'><input id="generateRandomNamesAliExpress" id="group2" type="checkbox" /> generate random names instead of aliexpress a reviewer name </span>
                                </div>

                                <div style="margin-bottom:15px">
                                    <span style='color:#899195; font-size:'><input id="imortOnlyFiveStarsAliExpress" id="group2" type="checkbox" /> Import only 5 stars reviews </span>
                                </div>

                                <div style="margin-bottom:15px">
                                    <span style='color:#899195; font-size:'><input id="importReviewsFlag" id="group2" type="checkbox" /> Import review flag </span>
                                </div>

                                <div style="margin-bottom:15px">
                                    <span style='color:#899195; font-size:'><input id="importProductDetails" id="group2" type="checkbox" /> Import review purchase detatils such as color, size, etc.. </span>
                                </div>

                                <div style="margin-bottom:15px">
                                    <span style='color:#899195; font-size:'><input id="importLogisticDetails" id="group2" type="checkbox" /> Import Logistic details</span>
                                </div>


                                <!-- <div style="margin-bottom:15px">
                                        <span style='color:#899195; font-size:'><input id="numberofReviewsByDefaultAliexpress" id="group2" type="checkbox" /> Number of reviews loaded by default </span>
                                    </div> -->
                                <!-- <div style="margin-bottom:15px">
                                        <span style='color:#899195; font-size:'><input id="customizeReviewsBeforeimportAliExpress" id="group2" type="checkbox" /> Customize reviews before import </span>
                                    </div> -->

                                <!-- <div style="margin-bottom:15px">
                                        <span style='color:#899195; font-size:'><input id="languageReviewsAliexpress" id="group2" type="checkbox" /> Select reviews language </span>
                                    </div> -->
<!-- 
                                <h4 style="font-weight:bold">
                                    Select language
                                </h4> -->

                                <div style='display:flex; display:none'>
                                    <div style="flex: 1 1 24%;">
                                        <div style="padding:10px"> <input type="radio" name="language" value="en_EN" checked="checked"> English<br></div>
                                        <div style="padding:10px"><input type="radio" name="language" value="fr_FR"> French<br></div>
                                        <div style="padding:10px"><input type="radio" name="language" value="es_ES"> Spanish<br></div>
                                        <div style="padding:10px"><input type="radio" name="language" value="it_IT"> Italian<br></div>
                                    </div>

                                    <div style="flex: 1 1 24%;">
                                        <div style="padding:10px"><input type="radio" name="language" value="de_DE"> German<br></div>
                                        <div style="padding:10px"><input type="radio" name="language" value="ru_RU"> Russian<br></div>
                                        <div style="padding:10px"><input type="radio" name="language" value="ar_AR"> Arabe<br></div>
                                        <div style="padding:10px"><input type="radio" name="language" value="pt_PT"> portuguese<br></div>
                                    </div>
                                    <div style="flex: 1 1 24%;">

                                        <div style="padding:10px"><input type="radio" name="language" value="ko_KO"> Korean<br></div>
                                        <div style="padding:10px"><input type="radio" name="language" value="tr_TR"> Turkish<br></div>
                                        <div style="padding:10px"><input type="radio" name="language" value="ar_AR"> Arabe<br></div>
                                        <div style="padding:10px"><input type="radio" name="language" value="vi_VI"> Vietnamese<br></div>
                                    </div>
                                    <div style="flex: 1 1 24%;">


                                        <div style="padding:10px"><input type="radio" name="language" value="th_TH"> Thailand<br></div>
                                        <div style="padding:10px"><input type="radio" name="language" value="pl_PL"> Polish<br></div>
                                        <div style="padding:10px"><input type="radio" name="language" value="he_HE"> Hebrew<br></div>
                                    </div>
                                </div> 



                            </div>


                            <div class="Global-import-configuration-section" style="flex: 1 1 48%; margin-left:1%; margin-right:2%; margin-top:2%;; padding:2%; border-radius:10px; background-color:white">


                                <h4 style="font-weight:bold">
                                    Amazon reviews configuration
                                </h4>



                                <div style="margin-bottom:15px; margin-top:20px">
                                    <span style='color:#899195; font-size:'><input id="importOnlyWithImagesAmazon" id="group1" type="checkbox" /> Import only reviews including images</span>
                                </div>

                                <div style="margin-bottom:15px">
                                    <span style='color:#899195; font-size:'><input id="onlyVerifiedPurchase" id="group2" type="checkbox" /> Only verified purchases reviews </span>
                                </div>

                                <div style="margin-bottom:15px">
                                    <span style='color:#899195; font-size:'><input id="imortOnlyFiveStarsAmazon" id="group2" type="checkbox" /> Import only 5 stars reviews </span>
                                </div>


                                <div style="margin-bottom:15px">
                                    <span style='color:#899195; font-size:'><input id="importProductTitleAmazon" id="group2" type="checkbox" /> Import Product title </span>
                                </div>
                                <div style="margin-bottom:15px">
                                    <span style='color:#899195; font-size:'><input id="importHelpfullStatement" id="group2" type="checkbox" /> Import Helpfull statement (example 32 people found this helpful) </span>
                                </div>


                                <div style="margin-bottom:15px">
                                    <span style='color:#899195; font-size:'><input  id="isHighQualityImage" id="group2" type="checkbox" /> Enable high quality images </span>
                                </div>



                                <!-- <div style="margin-bottom:15px">
                                        <span style='color:#899195; font-size:'><input id="numberofReviewsByDefaultAmazon" id="group2" type="checkbox" /> Number of reviews loaded by default </span>
                                    </div> -->
                                <div style="margin-bottom:15px">
                                    <label> seach reviews that include the following text</label>
                                    <input id="searchKeywordAmazon" class="form-control" id="group2" type="text" />
                                </div>

                                <!-- <div style="margin-bottom:15px">
                                        <span style='color:#899195; font-size:'><input id="languageReviewsAmazon" id="group2" type="checkbox" /> Select reviews language </span>
                                    </div> -->

<!-- 
                                    <h4 style="font-weight:bold">
                                    Select Amazon domains
                                </h4> -->

                                 <div style='display:flex; display:none'>
                                    <div style="flex: 1 1 19%;">
                                        <div style="padding:10px"> <input type="radio" name="languageAmazon" value="en_EN" checked="checked"> United states<br></div>
                                        <div style="padding:10px"><input type="radio" name="languageAmazon" value="uk_UK"> United kingdom<br></div>
                                       
                                    </div>

                                    <div style="flex: 1 1 19%;">
                                        <div style="padding:10px"><input type="radio" name="languageAmazon" value="de_DE"> Germany<br></div>
                                        <div style="padding:10px"><input type="radio" name="languageAmazon" value="it_IT"> Italia<br></div>
                                    </div>

                                    <div style="flex: 1 1 19%;">
                                        <div style="padding:10px"><input type="radio" name="languageAmazon" value="ca_CA"> Canada<br></div>
                                        <div style="padding:10px"><input type="radio" name="languageAmazon" value="fr_FR"> France<br></div>
                                    </div>

                                    <div style="flex: 1 1 19%;">
                                        <div style="padding:10px"><input type="radio" name="languageAmazon" value="jp_JP"> Japan<br></div>
                                        <div style="padding:10px"><input type="radio" name="languageAmazon" value="in_IN"> India<br></div>
                                    </div>

                                    <div style="flex: 1 1 19%;">
                                        <div style="padding:10px"><input type="radio" name="languageAmazon" value="es_ES"> Spain<br></div>
                                    </div>


                                </div>
 



                                <!-- </div> -->
                            </div>



                        </div>



                        <!-- ///////////////////////////////////////////// -->
                        <!-- ///////////////////////////////////////////// -->
                        <!-- ///////////////////////////////////////////// -->
                        <!-- ///////////////////////////////////////////// -->
                        <!-- ///////////////////////////////////////////// -->
                        <!-- ///////////////////////////////////////////// -->
                        <!-- ///////////////////////////////////////////// -->
                        <!-- ///////////////////////////////////////////// -->
                        <!-- ///////////////////////////////////////////// -->
                        <!-- ///////////////////////////////////////////// -->
                        <!-- ///////////////////////////////////////////// -->










                        <!-- ///////////////////////////////////////////// -->
                        <!-- ///////////////////////////////////////////// -->
                        <!-- ///////////////////////////////////////////// -->
                        <!-- ///////////////////////////////////////////// -->
                        <!-- ///////////////////////////////////////////// -->
                        <!-- ///////////////////////////////////////////// -->
                        <!-- /////////////////////ORDER//////////////////////// -->
                        <!-- //////////////////////////ORDER/////////////////// -->
                        <!-- ///////////////////////////////ORDER////////////// -->
                        <!-- ///////////////////////////////////////////// -->
                        <!-- ///////////////////////////////////////////// -->












                        <button id="saveGlobalConfigurationReviewPlugin" class="btn btn-primary" style="margin-top:20px; width:20%; margin-left:40%"> Save configuration</button>
                        <div id="savedCorrectlySection" style="color:red; display: none"> Configuration has been saved correctly </div>



                    </div>

                </div>

                <div class="tab-pane active in" id="pills-products" role="tabpanel" aria-labelledby="pills-products-products">

                    <div class="loader2" style="display:none; z-index:9999">
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                    </div>


                    <div style="border-radius:10px;  padding:7px; margin: 20px;  color:red; font-family: monospace; box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;">
                                    Faster import, Directly from Amazon and aliExpress website ?


                        <button class="button-5">
                            <a style="color:black" href="https://chrome.google.com/webstore/detail/amazon-and-aliexpress-rev/folccdmdpbpllglnhokbihjedgedjabb" target="_blank" >
                            Install the chrome extension from here
                            </a>

                        </button>
                    </div>



                    <div><label style="background-color:#f3f5f6;color:black;border-radius: 5px; padding:15px; width:98%"> Search product<label></div>



                    <!-- <label>Search product by sku</label> -->

                    <!-- <button style="width:100%; margin-top:20px; margin-bottom:20px" class="btn btn-danger" id="updateAllProductsAliExpress" disabled>Update all products By Category - coming soon</button> -->

                    <div class="input-group">
                        <input type="text" style="width:99%" class="form-control" id="skusearchValue" placeholder='Search by id' />

                        <span class="input-group-btn">
                            <button style="  margin-bottom:20px" class="btn btn-primary" id="searchBySku">Search by Id</button>
                        </span>
                    </div>
                    <!-- <button style="width:20%; margin-left: 42%; margin-top:20px; margin-bottom:20px" class="btn btn-default btn-lg" id="updateCurrentPage">Update Stock and price on current page</button> -->
                    <!-- <label class="currencyDetails" style="margin-top:10px; padding:10px; width:98%; background-color:#f3f5f6;color:black; border-radius: 5px;">
                               
                               </label> -->





                    <div class="log-sync-product" style="background-color:white; padding:5px; max-height:500px; overflow-y:scroll">
                        <!-- <div><label style="background-color:#f3f5f6;color:black; padding:10px; width:98%"> Updaing product log<label></div> -->

                    </div>
                    <!-- <div><label style="background-color:#f3f5f6;color:black; padding:10px; width:98%"> List of products<label></div> -->

                    <table id="products-wooshark-reviews" class="table table-striped">
                        <thead>
                            <tr>
                                <th width="10%">image</th>
                                <th width="10%">sku</th>
                                <th width="10%">id</th>
                                <th width="25%">title</th>
                                <th width="14%">Import Reviews </th>

                            </tr>
                        </thead>

                    </table>


                    <div class="loader2" style="display:none">
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                    </div>



                    <nav aria-label="product-pagination-reviews" style="text-align:center;">
                        <ul id="product-pagination-reviews" class="pagination pagination-lg justify-content-center">
                            <!-- <li id="product-page-1" class="-product-page-item"><a class="page-link active active">1</a></li> -->

                            <!-- <li class="page-item"><a class="page-link" href="#">2</a></li> -->
                            <!-- <li class="page-item"><a class="page-link" href="#">3</a></li> -->
                        </ul>
                    </nav>



                </div>

                

                <div class="tab-pane fade" id="how-it-works" role="tabpanel" aria-labelledby="how-it-works-tab" style="background-color:#f3f5f6">
                    <div style="margin-left:20%">

                    <iframe width="60%" height="450" src="https://www.youtube.com/embed/cnzEyn_2dxI" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>            

                    </div>
                </div>

                <div class="tab-pane fade" id="go-pro" role="tabpanel" aria-labelledby="go-pro-tab" style="background-color:#f3f5f6">



                <button class="button-7" style="width:20%; margin-left:40%; margin-top:15px; "><a class="" style="color:white" href="https://sharkdropship.com/irivyou" target="_blank"> GO PRO from here</a> </button>
                    <div>
                        <iframe width="45%" style="margin: 20px;" height="315" src="https://www.youtube.com/embed/ho8qQrRLWP0" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                        <iframe width="45%" style="margin: 20px;" height="315" src="https://www.youtube.com/embed/W4uzzvB6Sqo" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                            <!-- <iframe width="30%" style="margin: 20px;" height="315" src="https://www.youtube.com/embed/TYINWrZpRD4" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe> -->
                    </div>
                    <div style="display:flex">
                        <h4 style="    font-family: inherit; text-align:center; flex: 1 1 20%; margin:2%;    border: 1px solid #cbbfbf; padding:2%; border-radius:10px; background-color:white">
                            chrome extension & wordpress plugin
                        </h4>
                        <h4 style="    font-family: inherit; text-align:center; flex: 1 1 20%; margin:2%;    border: 1px solid #cbbfbf; padding:2%; border-radius:10px; background-color:white">
                            Unlimited import
                        </h4>
                        <h4 style="    font-family: inherit; text-align:center; flex: 1 1 20%; margin:2%;    border: 1px solid #cbbfbf; padding:2%; border-radius:10px; background-color:white">
                            Multi-stores support license
                        </h4>
                        <h4 style="    font-family: inherit; text-align:center; flex: 1 1 20%; margin:2%;    border: 1px solid #cbbfbf; padding:2%; border-radius:10px; background-color:white">
                            Future updates
                        </h4>
                        <h4 style="    font-family: inherit; text-align:center; flex: 1 1 20%; margin:2%;    border: 1px solid #cbbfbf; padding:2%; border-radius:10px; background-color:white">
                            Full support
                        </h4>
                      
                    </div>



                   
                <!-- <div style="border-radius:10px;  padding:7px; margin: 20px;  color:red; font-family: monospace; box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;">
                                Faster import, Directly from Amazon and aliExpress website ?


                                <button class="button-5">
                            <a style="color:black" href="https://chrome.google.com/webstore/detail/amazon-and-aliexpress-rev/folccdmdpbpllglnhokbihjedgedjabb" target="_blank" >
                            Install the chrome extension from here
                            </a>

                        </button>
                    </div>


                <button class="btn btn-primary" style="width:64%%;"> <a class="fa fa-star fa-3X" style="color:black" href="http://sharkdropship.com/irivyou" target="_blank"> GO PRO for unlimited reviews import </a></button>
                

              


                <h3>How the chrome extension works ? </h3>

                <iframe width="560" height="315" src="https://www.youtube.com/embed/ho8qQrRLWP0" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>


                    <h3>How the plugin works ? </h3>

                    <iframe width="560" height="315" src="https://www.youtube.com/embed/W4uzzvB6Sqo" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

                
                <h2>Premuim features</h2>
                <p>&nbsp;</p>
                <p>- Load Reviews by 50 and by 100 at once</p>
                <p>- unlimited import using the chrome extension and 8000 import per month using the pluginh</p>
                <p>- Full support</p>
                <p>- Import high quality images with reviews</p>
                <p>- Future updates  </p>
                <p>- continuous updates  </p>
                <p>- Other features </p>
                <p>&nbsp;</p> -->
                </div>

                <div class="tab-pane fade" id="support" role="tabpanel" aria-labelledby="support-tab" style="background-color:#f3f5f6">
                 
        <!-- #######  YAY, I AM THE SOURCE EDITOR! #########-->
<h2 style="color: #2e6c80;">Frequently asked questions</h2>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p><span style="color: #ff6600;"><strong>Question 1: I cannot see reviews on the tab reviews, but I can see the counter</strong></span></p>
<p><strong><span style="color: #ff0000;">Answer:&nbsp;</span></strong></p>
<p><strong>Step 1: </strong>&nbsp;First thing to do is to check the menu 'comments' on wordpress menu, if the you see the reviews, this means that the reviews were imported correctly but something is preventing them from being displayed, if you have one of the following plugin installed then disabled them (ryviu, WooCommerce Photo Reviews) go with</p>
<p><strong>Step 2:</strong>&nbsp;check WooCommerce product options, located at <strong>WordPress admin dashboard -&gt; WooCommerce -&gt; Settings -&gt; Products -&gt; Reviews -&gt; Enable reviews</strong></p>
<p>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;</p>
<p><strong>Step 3:</strong>&nbsp;Reviews still not showing up? Make sure that reviews are enabled for the individual product in the&nbsp;<strong>Product data</strong>&nbsp;metabox.</p>
<p>&nbsp;</p>
<p><strong>Step 4:</strong> If you still can&rsquo;t enable the reviews, try temporarily switching to a&nbsp;<a href="https://codex.wordpress.org/WordPress_Default_Themes" target="_blank" rel="noreferrer noopener" aria-label="default WordPress theme (opens in a new tab)">default WordPress theme</a>, such as Twenty Fifteen. When you see that reviews show up with another theme then you can contact your theme developer to find out why their theme doesn&rsquo;t show them</p>
<p>&nbsp;</p>
<p><strong>Sep 5:</strong> You can try disabling all plugins except for WooCommerce to see if any of them may be preventing the reviews from showing up.</p>
<p>&nbsp;</p>
<p>These Steps should be enough to solve the issue with reviews not being displayed.&nbsp;</p>
<p>&nbsp;</p>
<p><span style="color: #ff6600;"><strong>Question 2: I am trying to load reviews for a specific ASIN but it show no reviews to load.</strong></span></p>
<p><span style="color: #ff6600;"><span style="color: #ff0000;"><strong>Answer</strong></span></span></p>
<p><span style="color: #ff6600;"><span style="color: #000000;">make sure you have selected the appropriate amazon domains, some asin are not available cross domains, thus as a result you will need to select the appropriate amazon domain from configuration tab</span></span></p>
<p>&nbsp;</p>
<p><span style="color: #ff6600;"><strong>Question 3: How can I import high quality images&nbsp; ?</strong></span></p>
<p><span style="color: #ff6600;"><span style="color: #ff0000;"><strong>Answer</strong></span></span></p>
<p><span style="color: #ff6600;"><span style="color: #000000;">From the configuration tab, you enable the option import high quality images, note that this feature is a premuim feature not available on the free version.</span></span></p>
<p>&nbsp;</p>
<p><span style="color: #ff6600;"><strong>Question 4: I am not able to import reviews after reaching the limit&nbsp; ?</strong></span></p>
<p><span style="color: #ff6600;"><span style="color: #ff0000;"><strong>Answer</strong></span></span></p>
<p><span style="color: #ff6600;"><span style="color: #ff0000;"><span style="color: #000000;">The counter will reset every 7 days, the limit will be again set to 0 after this time, if there is any issue please contact our support team&nbsp;</span></span></span></p>
<p>&nbsp;</p>
<p><span style="color: #ff6600;"><strong>Question 5: Wht is the reviews library and how can I use it</strong></span></p>
<p><strong style="color: #ff0000;">Answer</strong></p>
<p><span style="color: #ff6600;"><span style="color: #ff0000;"><span style="color: #000000;">The reviews library is a kind of library where the user can save most common reviews, the user can load this list whenever he want by a simple click. this can be used for similar products, so instead of loading reviews by asin each time, he can create a library&nbsp;</span></span></span></p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p><span style="color: #ff6600;"><strong>Question 6: How to contact the support ?</strong></span></p>
<p><strong style="color: #ff0000;">Answer</strong></p>
<p>you can contact the support by using our website <a href="http://sharkdropship.com/contact-us">http://sharkdropship.com/contact-us</a></p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p><strong>&nbsp;</strong></p>

                </div>




                <div class="tab-pane fade" id="reviews-library" role="tabpanel" aria-labelledby="reviews-library" style="background-color:#f3f5f6">
                    <!-- <button class="btn btn-primary" style="width:30%; margin-left:35%"> <a class="fa fa-star fa-3X" href="http://sharkdropship.com/irivyou" target="_blank"> Reviews library </a></button> -->
                  

                    <div style="border-radius:10px;  padding:7px; margin: 20px;  color:red; font-family: monospace; box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;">
                                        Faster import, Directly from Amazon and aliExpress website ?


                                        <button class="button-5">
                            <a style="color:black" href="https://chrome.google.com/webstore/detail/amazon-and-aliexpress-rev/folccdmdpbpllglnhokbihjedgedjabb" target="_blank" >
                            Install the chrome extension from here
                            </a>

                        </button>
                    </div>


                    
                    <input placeholder="seach review by keyword" id="reviewByKeyword" class="form-control"/>
                    <button class="btn btn-primary" id="searchReviewByKeyword"> Search reviews</button>

                    <!-- <label> Reviews Library</label> -->
                    <table id="reviews-library-table" class="table table-striped">
                        <thead>
                            <tr>

                            <th width="5%">Id</th>
                                <th width="30%">Review content</th>
                                <th width="10%">username</th>
                                <th width="10%">date Creation</th>
                                <th width="10%">Rating </th>
                                <th width="10%">Email</th>
                                <th width="10%">Remove </th>
                                <!-- <th width="10%">update </th> -->

                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
               
                    <!-- <button class="btn bt-primary" style="width:20%; margin-left:40%" id="persistREviewLibrary"> Save updates</button> -->
                </div>

                



                <div class="tab-pane fade" id="pills-activation" role="tabpanel" aria-labelledby="pills-activation-tab" style="background-color:#f3f5f6">
                    <div style="height:20px; color:grey"></div>

                    <div style="background-color:white; padding:2%; margin:2%">
                        <div style="margin-tpp:20px">
                            <h4> Activate your license from here</h4>
                            <input id="licenseValueReviewsPlugin" placeholder="please paste your license received by email here" class="form-control" style="width:100% margin-top:20px" />
                            <button class="btn btn-primary" style="width:100%" id="titiToto"> Check and Activate </button>
                        </div>
                    </div>
                    <div style="height:20px; color:grey"></div>

                </div>







                <div id="modal-container"> </div>

                <!-- <button type="button" style="margin:10px; "  class="btn btn-primary" data-toggle="modal" data-target=".bd-example-modal-lg"> Import reviews to product</button> -->
                <!-- <div class="modal fade" id="myModalReviews" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true" style="z-index:99999">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                            </div>
                            <div class="modal-body">
                                <button type="button" style="width:25%; margin-top:10px; display:block" class="btn btn-primary" id="addReview" style="width:50%;margin-top:10px"> Add Review</button>

                                <div id="customReviews" style="overflow-y:scroll;height:500px">
                                    <table id="table-reviews" class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Review</th>
                                                <th>Username</th>
                                                <th>email</th>
                                                <th>Date creation</th>
                                                <th>Rating</th>
                                                <th>Remove</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" id="confirmReviewInsertion" class="btn btn-primary" data-dismiss="modal">Insert Reviews</button>

                                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div> -->
            </div>

            <?php

                }

                /**
                 * Helper-function outputs the correct form element (input tag, select tag) for the given item
                 * @param  $aOptionKey string name of the option (un-prefixed)
                 * @param  $aOptionMeta mixed meta-data for $aOptionKey (either a string display-name or an array(display-name, option1, option2, ...)
                 * @param  $savedOptionValue string current value for $aOptionKey
                 * @return void
                 */
                protected function createFormControl($aOptionKey, $aOptionMeta, $savedOptionValue)
                {
                    if (is_array($aOptionMeta) && count($aOptionMeta) >= 2) { // Drop-down list
                        $choices = array_slice($aOptionMeta, 1);
                        ?>
                <p><select name="<?php echo $aOptionKey ?>" id="<?php echo $aOptionKey ?>">
                        <?php
                                    foreach ($choices as $aChoice) {
                                        $selected = ($aChoice == $savedOptionValue) ? 'selected' : '';
                                        ?>
                            <option value="<?php echo $aChoice ?>" <?php echo $selected ?>><?php echo $this->getOptionValueI18nString($aChoice) ?></option>
                        <?php
                                    }
                                    ?>
                    </select></p>
            <?php

                    } else { // Simple input field
                        ?>
                <p><input type="text" name="<?php echo $aOptionKey ?>" id="<?php echo $aOptionKey ?>" value="<?php echo esc_attr($savedOptionValue) ?>" size="50" /></p>
        <?php

                }
            }

            /**
             * Override this method and follow its format.
             * The purpose of this method is to provide i18n display strings for the values of options.
             * For example, you may create a options with values 'true' or 'false'.
             * In the options page, this will show as a drop down list with these choices.
             * But when the the language is not English, you would like to display different strings
             * for 'true' and 'false' while still keeping the value of that option that is actually saved in
             * the DB as 'true' or 'false'.
             * To do this, follow the convention of defining option values in getOptionMetaData() as canonical names
             * (what you want them to literally be, like 'true') and then add each one to the switch statement in this
             * function, returning the "__()" i18n name of that string.
             * @param  $optionValue string
             * @return string __($optionValue) if it is listed in this method, otherwise just returns $optionValue
             */
            protected function getOptionValueI18nString($optionValue)
            {
                switch ($optionValue) {
                    case 'true':
                        return __('true', 'wooreviews-importer');
                    case 'false':
                        return __('false', 'wooreviews-importer');

                    case 'Administrator':
                        return __('Administrator', 'wooreviews-importer');
                    case 'Editor':
                        return __('Editor', 'wooreviews-importer');
                    case 'Author':
                        return __('Author', 'wooreviews-importer');
                    case 'Contributor':
                        return __('Contributor', 'wooreviews-importer');
                    case 'Subscriber':
                        return __('Subscriber', 'wooreviews-importer');
                    case 'Anyone':
                        return __('Anyone', 'wooreviews-importer');
                }
                return $optionValue;
            }

            /**
             * Query MySQL DB for its version
             * @return string|false
             */
            protected function getMySqlVersion()
            {
                global $wpdb;
                $rows = $wpdb->get_results('select version() as mysqlversion');
                if (!empty($rows)) {
                    return $rows[0]->mysqlversion;
                }
                return false;
            }

            /**
             * If you want to generate an email address like "no-reply@your-site.com" then
             * you can use this to get the domain name part.
             * E.g.  'no-reply@' . $this->getEmailDomain();
             * This code was stolen from the wp_mail function, where it generates a default
             * from "wordpress@your-site.com"
             * @return string domain name
             */
            public function getEmailDomain()
            {
                // Get the site domain and get rid of www.
                $sitename = strtolower($_SERVER['SERVER_NAME']);
                if (substr($sitename, 0, 4) == 'www.') {
                    $sitename = substr($sitename, 4);
                }
                return $sitename;
            }
        }
