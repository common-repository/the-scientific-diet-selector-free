jQuery(function () {    
    "use strict";
    jQuery("#addProblemButton").on("click", function f(e) {
        //this is for adding a new problem-solution pair with the correct ids
        e.preventDefault();
        var nextKey = jQuery("#dietProblemInputsDiv").children().length + 1;
        var toAppend = "<div>Problem:<br><input size='50' type='text' name='tsdsf_diet_problems[" + nextKey + "]' value='Insert new problem here.' /><br/>Solution:<br><input size='50' type='text' name='tsdsf_diet_solutions[" + nextKey + "]' value='Insert new solution here!' /><br/><a href='' class='tsdsf_remove_problem'>Delete this</a><br/><br/></div>";
        jQuery("#dietProblemInputsDiv").append(toAppend);
    });
    
    jQuery(".getPremiumButton").on("click", function f(e) {
        e.preventDefault();
        window.open(
          'https://scientificdiets.com/getpremium',
          '_blank'
        );
        
    });
    
    jQuery(".tsdsf_remove_problem").on("click", function f(e) {
        e.preventDefault();
        jQuery(this).closest('div').remove();
    });
    
    
    
});





