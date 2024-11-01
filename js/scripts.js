jQuery(function () {    
    "use strict";
    
    
    jQuery.each(jQuery(".tsdsf_slider"), function (index, slider) {
        jQuery(slider).on("input change", function() {            
            var badge = jQuery(".tsdsf_badge[data-id='" + jQuery(slider).data("id") + "']");
            var grade = scaleToVerbal(this.value);
            badge.html(this.value + " - " + "'" + grade + "'");
            //we need these when submitting
            jQuery(slider).attr("data-isused", "true");            
            
        });
    });
    

    
    
    jQuery("#recsDiv").accordion({heightStyle: "content", collapsible: true, active: false });
    jQuery("#nativeAdDiv").accordion({heightStyle: "content", collapsible: true, active: false});

    jQuery("#continueToPersonalData").on("click", function f(e) {
        jQuery("#dchooser_splash").hide();
        jQuery("#dchooser_step_personaldata").show();
    });
    
    jQuery("#continueToSliders").on("click", function f() {
        jQuery("#dchooser_splash").hide();
        jQuery("#dchooser_step_personaldata").hide();
        jQuery("#dchooser_step_sliders").show();
    });

    jQuery("#revealResultsButton").on("click", function f(e) {
        e.preventDefault();
        var sliders = jQuery(".tsdsf_slider[data-isused='true']");
        if (sliders.length == 0) {
            displayAlert("Provide at least one value.");
            return;
        }
        jQuery("#dchooser_step_sliders").hide();
        jQuery("#dchooser_step_results").show();
        
        
        getSupport();
    });

    jQuery("#adjustCriteriaButton").on("click", function f(e) {
        e.preventDefault();

        jQuery("#dchooser_step_results").hide();
        jQuery("#dchooser_step_sliders").show();
        jQuery("#recsDiv").html("<p><strong>Just a sec...crunching data...</strong></p>");
    });

    jQuery("#resetCriteriaButton").on("click", function f(e) {
        resetCriteria();
    });  

    jQuery("#cancelRequestHelpLink").on("click", function f(e) {
        e.preventDefault();
        jQuery("#dchooser_step_leadcapture").hide();
        jQuery("#dchooser_step_results").show();
    });  

    jQuery("#contactMeButton").on("click", function (event) {
        jQuery("#dchooser_step_results").hide();
        jQuery("#dchooser_step_leadcapture").show();
    });

    jQuery("#requestHelpButton").on("click", function f(e) {
        e.preventDefault();
        var name = "";
        var email = "";
        var gender = "";
        var age = "";
        var goalAmount = "";
        var problem = "";

        name = jQuery('#dchooser_nameinput').val();
        email = jQuery('#dchooser_emailinput').val();

        
        gender = jQuery("#genderdiv input[type='radio']:checked").val();
        age = jQuery('#dchooser_ageinput').val();
        if(age == "") {
            age = "-1";
        }
        
        goalAmount = jQuery('#dchooser_weightlossgoal').find(":selected").val(); 

        if (jQuery('#dchooser_biggestproblem').length){
            problem = jQuery('#dchooser_biggestproblem').find(":selected").val(); 
        }
        if(name == "" || email == "") {
            displayAlert("Please fill in all fields.");
            return;
        }

        jQuery.ajax({
		url : ajax_object.ajaxurl,
		type : 'post',
		data : {
            action: "tsdsf_submit_email",
            name: name,
            email: email,
            age: age,
            gender: gender,
            goal_amount: goalAmount,
            problem: problem,
		},
		success : function( response ) {
            jQuery("#dchooser_step_leadcapture").empty();
            jQuery("#dchooser_step_leadcapture").html("<h3 class='center-block mt-3'>Thanks!</h3><p class='center-block'>We'll be in touch very soon – stay tuned!</p>");
		}
	});



    });
    
});

function ratingsToVerbal(val) {
    var grade = "NA";
    if (val == 0) {
        grade = "Zero";
    } else if (val > 0 && val < 21) {
        grade = "Very poor";
    } else if (val > 20 && val < 41) {
        grade = "Below average";
    } else if (val > 40 && val < 61) {
        grade = "Average";
    } else if (val > 60 && val < 81) {
        grade = "Above average";
    } else if (val > 80) {
        grade = "Excellent";
    }
    return grade;
}

function scaleToVerbal(val) {
    var grade = "NA";
    if (val == 0) {
        grade = "Does not matter at all";
    } else if (val > 0 && val < 21) {
        grade = "Not important";
    } else if (val > 20 && val < 41) {
        grade = "Slightly important";
    } else if (val > 40 && val < 61) {
        grade = "Fairly important";
    } else if (val > 60 && val < 81) {
        grade = "Important";
    } else if (val > 80) {
        grade = "Very important";
    }
    return grade;
}

function getSupport() {
    document.querySelector('#tsdsfEmbedContainer').scrollIntoView({behavior: 'smooth'});
    var sliders = jQuery(".tsdsf_slider[data-isused='true']");
    jQuery("#dchooser_step_results").show();

    var ratingsArray = [[]];
    sliders.each(function () {
        var ratedValue = this.value;
        var critId = jQuery(this).data("id");
        var rating = [critId, ratedValue]
        ratingsArray.push(rating);
    });
    ratingsArray.shift(); //kill the first empty elem.. push makes a new elem always
    var jsonEncoded = JSON.stringify(ratingsArray, null, 2);
    

    
    jQuery.ajax({
		url : ajax_object.ajaxurl,
		type : 'post',
		data : {
            action: "tsdsf_fetch_results",
            criteria_importances: jsonEncoded
		},
		success : function( response ) {
            var recs = jQuery.parseJSON( response );
            displayRecommendations( recs );
		}
	});
}

function displayAlert(msg){
    jQuery("#warningDiv").show();
    jQuery("#warningDiv").html("<p style='color:red; text-align: center;'><b>" + msg + "</b></p>");
    
    setTimeout(function() { 
        jQuery("#warningDiv").hide();
    }, 2000);
    
}

function displayRecommendations(recs) {
    jQuery("#recsDiv").empty();

    if (recs.length == 0) {
        displayAlert("Provide more requirements - not enough data to provide recommendations!");
        return;
    }
    var index = 1;
    jQuery.each(recs, function () {
        
        var additionalInfo = "<div class='tsdsf_row'><div class='tsdsf_col-12'><p>" + this.option_details + " <a target='_blank' href='" + all_diets_array[this.option_id]["primary_link"] + "'>Click here to learn more.</a></p></div></div>";
        
        var htmlStr = "<div class='mb-2'><p><a href>" + index + ".) " + this.option_title + "</a></p></div><div class='mb-2'>" + additionalInfo + "</div>";

        jQuery("#recsDiv").append(htmlStr);
        index++;
    });
    
    jQuery("#recsDiv").accordion( "refresh" );
    
    jQuery("#promotionBanner").show();
    jQuery("#contactMeButton").show();
    jQuery("#nativeAdBanner").show();
    
    if (jQuery('#dchooser_biggestproblem').length){
        var problem = jQuery('#dchooser_biggestproblem').find(":selected").val();
        if (problem != -1){
            var solution = all_problems_array[problem][1];
            jQuery("#problemHolder").html(solution);
            jQuery("#problemHolder").show();
        }  
    }
}


function resetCriteria() {
    jQuery("#recsDiv").empty();
    var sliders = jQuery(".tsdsf_slider");
    sliders.each(function () {
        this.value = 0;
        jQuery(this).attr("data-isused", "false");

        var badge = jQuery(".tsdsf_badge[data-id='" + jQuery(this).data("id") + "']");
        badge.html("Does not matter at all");
    });
}



