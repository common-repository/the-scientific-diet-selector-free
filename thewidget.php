<?php
$valid_licence = get_option( 'tsdsf_valid_licence' );
//$cbid = get_option( 'tsdsf_clickbank_id' );
//$unlockWeightLossURL = 'http://' . $cbid . '.kaizendiet.hop.clickbank.net/';
//the actual content for promotion box
$promotion = get_option( 'tsdsf_promotionbody' );
//native ad title
$nativeAdTitle = get_option( 'tsdsf_nativetitle' );
//native ad body content
$nativeAdBody = get_option( 'tsdsf_nativebody' );
//show native ad? 
$showNative = get_option( 'tsdsf_check_nativead' );
//show promo box under the recommendations?
$showPromoBox = get_option( 'tsdsf_check_promobox' );
//show custom problem-solution pairs?
$showCustomProblems = get_option( 'tsdsf_check_customproblems' );
//shall we send leads to a webhook?
$showLeadCollect = get_option( 'tsdsf_check_leadcollector' );
//donate data and link to plugin homepage?
$showExternalLinks = get_option( 'tsdsf_check_donatedata' );

//custom problems that we want to provide a solution to
$diet_problems = get_option("tsdsf_diet_problems");


?>
<div class="tsdsf_css_scope">
    <div class="tsdsf_embedContainer" id="tsdsfEmbedContainer">
        <div class="tsdsf_row">
            <div class="tsdsf_col-12">
                <div class="tsdsf_content">
                    <div style="text-align: center;" id="dchooser_splash">
                        
                        <img class="mx-auto d-block" width="250px" src="<?php echo plugin_dir_url( __FILE__ ) . 'assets/logo.png'; ?>">  

                        <h4>FIND A DIET THAT WORKS!</h4>

                        <p>Discover the #1 diet that helps you lose weight, based on thousands of ratings of popular weight loss diets by people just like you.<br><br>It's 100% FREE and takes only seconds to get started!</p>

                            
                        <button id="continueToPersonalData">CLICK TO START</button>
                            
<?php if($showExternalLinks == "on") : ?>
                            <p><a target="_blank" href="http://scientificdiets.com/contribute">Or read more and contribute!</a></p>
                            
<?php endif; ?>

                    </div>
                    <div id="dchooser_step_personaldata" class="tsdsf_initiallyhidden">
                        
                        <h3 class="">Step 1: personal details:</h3>
                        <p>We'll use this data to optimize your suggestions</p>
                        <form>
                            <div class="tsdsf_formrow">
                                <label class="" for="genderdiv">Gender: </label>
                                <div id="genderdiv" >
                                        <label class="">
                                            <input class="" type="radio" name="genderRadios" id="maleRadio" value="male" required> Male
                                        </label>
                                        <label class="">
                                            <input class="" type="radio" name="genderRadios" id="femaleRadio" value="female" required> Female
                                        </label>
                                </div>
                            </div>
                            <div class="tsdsf_formrow">
                                <label class="" for="dchooser_ageinput"><b>Age</b></label><br>
                                <input required type="number" class="" id="dchooser_ageinput" placeholder="Your Age" style="">
                            </div>
                            <div class="tsdsf_formrow">
                                <label class="" for="dchooser_weightlossgoal"><b>Your weight loss goal?</b></label><br>
                                <select style="" class="" id="dchooser_weightlossgoal">
                                    <option value="-1" selected>Choose one...</option>
                                    <option value="1">1-5 kgs (2-10 lbs)</option>
                                    <option value="2">6-10 kgs (12-20 lbs)</option>
                                    <option value="3">More than 10 kg (more than 20 lbs)</option>
                                </select>
                            </div>
<?php if($showCustomProblems == "on" && $valid_licence == 1) : ?>


                            <div class="tsdsf_formrow">
                                <label class="formstext" for="dchooser_biggestproblem"><b>Which one of the following is the biggest problem for you?</b></label><br>
                                <select style="width: 50%;" class="" id="dchooser_biggestproblem">
                                    <option value="-1" selected>Choose one...</option>
<?php 
    foreach($diet_problems as $index => $value) {
        $descr = $value[0];
        echo "<option value='$index'>$descr</option>";
    }
?>
                                </select>
                            </div>
<?php endif; ?>
                        </form>
                        <button id='continueToSliders' style="float: left" class='btn btn-success btn-md mt-2'>CONTINUE -></button>
                    </div>
                    <div id="dchooser_step_sliders" class="tsdsf_initiallyhidden">
                        <h3>What matters in your diet?</h3>
                        <p class="">And how much? Use the sliders to tell us:</p>
                        <div>
                            <p class="">Helps lose weight fast <span data-id="6" class="tsdsf_badge">Does not matter at all</span></p>
                        </div>
                        <div class="tsdsf_slidecontainer">
                            <input data-id="6" type="range" min="0" max="100" width="100%" value="-1" class="tsdsf_slider">
                        </div>

                        <div>
                            <p class="">Suitable for long-term consumption <span data-id="7" class="tsdsf_badge">Does not matter at all</span></p>
                        </div>
                        <div class="tsdsf_slidecontainer">
                            <input data-id="7" type="range" min="0" max="100" width="100%" value="-1" class="tsdsf_slider">
                        </div>

                        <div>
                            <p class="">Generally recommended by others <span data-id="11" class="tsdsf_badge">Does not matter at all</span></p>
                        </div>
                        <div class="tsdsf_slidecontainer">
                            <input data-id="11" type="range" min="0" max="100" width="100%" value="-1" class="tsdsf_slider">
                        </div>

                        <div>
                            <p class="">Does not require a lot of willpower <span data-id="9" class="tsdsf_badge">Does not matter at all</span></p>
                        </div>
                        <div class="tsdsf_slidecontainer">
                            <input data-id="9" type="range" min="0" max="100" width="100%" value="-1" class="tsdsf_slider">
                        </div>

                        <!--div>
                            <p class="">Is highly nutritious<span data-id="10" class="tsdsf_badge">Does not matter at all</span></p>
                        </div>
                        <div class="tsdsf_slidecontainer">
                            <input data-id="10" type="range" min="0" max="100" width="100%" value="-1" class="tsdsf_slider">
                        </div-->

                        <div>
                            <p class="">Is cheap to follow <span data-id="8" class="tsdsf_badge">Does not matter at all</span></p>
                        </div>
                        <div class="tsdsf_slidecontainer">
                            <input data-id="8" type="range" min="0" max="100" width="100%" value="-1" class="tsdsf_slider">
                        </div>
                        
                        

                        <button id='revealResultsButton' style='float: right'>FIND NOW! -></button>
                        <button id='resetCriteriaButton' style='float: left'>RESET</button>
                    </div>
                    <div id="dchooser_step_results" class="tsdsf_initiallyhidden">
                        <h3>The best diets for you are:</h3>
                        <p>Click to expand...</p>
                        <div id="recsDiv">
                            <p><strong>Just a sec...crunching data...</strong></p>
                        </div>
<?php if($showNative == "on") : ?>
                        <div class="tsdsf_row tsdsf_initiallyhidden" id="nativeAdBanner">
                                <div id="nativeAdDiv">
                                    <p>Featured: <a href><?php echo $nativeAdTitle; ?></a></p>
                                    <div>
                                        <p><?php echo $nativeAdBody; ?></p>
                                    </div>
                                </div>
                        </div>
<?php endif; ?>

<?php if($showPromoBox == "on") : ?>
                       <div id="promotionBanner" class="tsdsf_promo_container tsdsf_initiallyhidden tsdsf_row">
                           <div>
                               <p><?php echo $promotion; ?></p>
                           </div>
                       </div>
<?php endif; ?>
                        <div style="text-align: center;" class="tsdsf_row">
                            <div id="problemHolder" class="tsdsf_initiallyhidden">
                            </div>
                        </div>

                        
                        <div class="tsdsf_row">
                            <button id='adjustCriteriaButton' style='float: left'>GO BACK</button>
<?php if($showLeadCollect == "on" && $valid_licence == 1) : ?>
                            <button id='contactMeButton' style="float: right" class='tsdsf_initiallyhidden'>CONTINUE -></button>
<?php endif; ?>
                        </div>
                    </div>
                    <div id="dchooser_step_leadcapture" class="tsdsf_initiallyhidden">
                        <h3 class="">Enter your details:</h3> 
                            <form>
                                <div class="tsdsf_row tsdsf_formrow">
                                    <label for="dchooser_nameinput">Name</label>
                                    <input required type="text" class="tsdsf_full_input" id="dchooser_nameinput" placeholder="Name">
                                </div>
                                <div class="tsdsf_row tsdsf_formrow">
                                    <label for="dchooser_emailinput">Email</label>
                                    <input required type="email" class="tsdsf_full_input" id="dchooser_emailinput" placeholder="Your email address">
                                </div>
                                <div style="text-align: center;" class="tsdsf_row tsdsf_formrow">
                                    <button type="submit" id='requestHelpButton' class=''>SEND MORE INFO!</button>
                                    <div style="margin-top: 10px;">
                                    <a href="#" id='cancelRequestHelpLink'>Go back</a>
                                    </div>
                                </div>
                            </form>
                    </div>
                </div>
            </div>
            <div class="tsdsf_col-12">
                <div class="warningdiv" id="warningDiv">
                </div>
            </div>
        </div>
    </div>
</div>

