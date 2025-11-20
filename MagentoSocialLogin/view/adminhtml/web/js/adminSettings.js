require(['jquery', 'jquery/ui'], function($){
    var $m = $.noConflict();
    $m(document).ready(function() {

        $m("#lk_check1").change(function(){
            if($("#lk_check2").is(":checked") && $("#lk_check1").is(":checked")){
                $("#activate_plugin").removeAttr('disabled');
            }
        });

        $m("#lk_check2").change(function(){
            if($("#lk_check2").is(":checked") && $("#lk_check1").is(":checked")){
                $("#activate_plugin").removeAttr('disabled');
            }
        });

        $m(".navbar a").click(function() {
            $id = $m(this).parent().attr('id');
            setactive($id);
            $href = $m(this).data('method');
            voiddisplay($href);
        });
        $m(".btn-link").click(function() {
            $m(this).siblings('.show_info').slideToggle('slow');
            
        });
        $m('#idpguide').on('change', function() {
            var selectedIdp =  jQuery(this).find('option:selected').val();
            $m('#idpsetuplink').css('display','inline');
            $m('#idpsetuplink').attr('href',selectedIdp);
        });
        $m("#mo_saml_add_shortcode").change(function(){
            $m("#mo_saml_add_shortcode_steps").slideToggle("slow");
        });
        $m('#error-cancel').click(function() {
            $error = "";
            $m(".error-msg").css("display", "none");
        });
        $m('#success-cancel').click(function() {
            $success = "";
            $m(".success-msg").css("display", "none");
        });
        $m('#cURL').click(function() {
            $m(".help_trouble").click();
            $m("#cURLfaq").click();
        });
        $m('#help_working_title1').click(function() {
            $m("#help_working_desc1").slideToggle("fast");
        });
        $m('#help_working_title2').click(function() {
            $m("#help_working_desc2").slideToggle("fast");
        });

    });
});

function setactive($id) {
    $m(".navbar-tabs>li").removeClass("active");
    $id = '#' + $id;
    $m($id).addClass("active");
}

function voiddisplay($href) {
    $m(".page").css("display", "none");
    $m($href).css("display", "block");
}

function mosp_valid(f) {
    !(/^[a-zA-Z?,.\(\)\/@ 0-9]*$/).test(f.value) ? f.value = f.value.replace(/[^a-zA-Z?,.\(\)\/@ 0-9]/, '') : null;
}

function showGoogleTestWindow() {
    var myWindow = window.open(googleTestURL, "TEST SOCIALLOGIN", "scrollbars=1 width=800, height=600");
}

function showFacebookTestWindow() {
    var myWindow = window.open(facebookTestURL, "TEST SOCIALLOGIN", "scrollbars=1 width=800, height=600");
}

function showLinkedinTestWindow() {
    var myWindow = window.open(linkedinTestURL, "TEST SOCIALLOGIN", "scrollbars=1 width=800, height=600");
}

function showTwitterTestWindow() {
    var myWindow = window.open(twitterTestURL, "TEST SOCIALLOGIN", "scrollbars=1 width=800, height=600");
}

function configure_google()
{
    document.getElementById("google_configurations").style.display = "block";
    document.getElementById("sociallogin_form").style.display = "none";
}

function cancel()
{
    document.getElementById("google_configurations").style.display = "none";
    document.getElementById("facebook_configurations").style.display = "none";
    document.getElementById("linkedin_configurations").style.display = "none";
    document.getElementById("twitter_configurations").style.display = "none";
    document.getElementById("sociallogin_form").style.display = "block";
}

function configure_facebook()
{
    document.getElementById("facebook_configurations").style.display = "block";
    document.getElementById("sociallogin_form").style.display = "none";
}

function configure_linkedin()
{
    document.getElementById("linkedin_configurations").style.display = "block";
    document.getElementById("sociallogin_form").style.display = "none";
}

function configure_twitter()
{
    document.getElementById("twitter_configurations").style.display = "block";
    document.getElementById("sociallogin_form").style.display = "none";
}

function mosocial_upgradeform(planType){
    jQuery('#requestOrigin').val(planType);
    jQuery('#mocf_loginform').submit();
}

//update---instead of checkbox using div

var registeredElement = document.getElementById("registered");
if (registeredElement) {
    registeredElement.addEventListener("click", ifUserRegistered, false);
}
function ifUserRegistered() {
    var inputField = document.getElementById("myInput");
    var confirmPasswordElement = jQuery('#confirmPassword');
    var checkAllTopicCheckBoxes = document.getElementById('registered');
    var registerLoginButton = document.getElementById('registerLoginButton');
    var register_login = document.getElementById('register_login');
    const forget = document.getElementById("forget_pass");
    var why_register_note = jQuery('.mo_note'); // Fixed selector
  
    if (confirmPasswordElement.css('display') === 'none') {
      // Register time
      confirmPasswordElement.css('display', 'block');
      registerLoginButton.value = "Register"; 
      checkAllTopicCheckBoxes.textContent = 'Log In';
      register_login.textContent = 'Register with miniOrange';
      confirmPasswordElement.prop('required', true); 
      forget.style.display = 'none';
      inputField.setAttribute("required", "required");
      why_register_note.css('display', 'block'); // Hide the note
    } else {
      // Login time
      confirmPasswordElement.css('display', 'none');
      registerLoginButton.value = "Login"; 
      checkAllTopicCheckBoxes.textContent = 'Register';
      register_login.textContent = 'Login with miniOrange';
      confirmPasswordElement.prop('required', false);
      why_register_note.css('display', 'none'); // Show the note
      forget.style.display = 'block'; 
      if (inputField.hasAttribute("required")) {
          inputField.removeAttribute("required");
      }
    }
  }

  
function supportAction(){
}
function login_icon_div() {
    var x = document.getElementById("social_login_icon_ui").value;
    var long_button = document.getElementById("long_button_id");
    var round_button = document.getElementById("round_button_id");
    var roundedge_button = document.getElementById("roundededge_button_id");
    var square_button = document.getElementById("square_button_id");
     if(x=="Long Button"){
       long_button.style.display = "block";
       round_button.style.display = "none";
       roundedge_button.style.display = "none";
       square_button.style.display = "none";
     }
     if(x=="Round"){
       long_button.style.display = "none";
       round_button.style.display = "block";
       roundedge_button.style.display = "none";
       square_button.style.display = "none";
     }
     if(x=="Rounded Edges"){
       long_button.style.display = "none";
       round_button.style.display = "none";
       roundedge_button.style.display = "block";
       square_button.style.display = "none";
     }
     if(x=="Square"){
       long_button.style.display = "none";
       round_button.style.display = "none";
       roundedge_button.style.display = "none";
       square_button.style.display = "block";
     }

 }