<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Kazaana</title>
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>css/style_prelaunch.css" />
</head>
<body>
<div class="wrapper"> 
  <div class="branding"><a href="<?php echo base_url(); ?>"><img src="<?php echo base_url(); ?>assets/images/logo.png" alt="Pixy Kids" /></a> </div>
  <div class="clearFix"></div>  
  <!-- Reference Form Starts -->
  <div class="formHolder" id="referenceDiv" style="display:none;">
    <div class="innerContent">
      <form name="prelaunchReference" id="prelaunchReference" method="post" onSubmit="javascript:callRefer();return false;">
      <input type="hidden" name="refererId" value="" id="refererId"/>
        <div class="formInner">
          <div class="formTitle"><img src="<?php echo base_url(); ?>assets/images/tell-your-friends.png" alt="Keep Me Posted" width="230" /></div>
                <div class="offerConf"><img src="<?php echo base_url(); ?>assets/images/text.png" alt="Offer Text" align="middle" /></div>
          <div class="formInputHolder">
            <div class="left"></div>
            <div class="rightInput">
            	<input type="text" class="right" name="refererEmail[]" value="" id="email0" onFocus="javascript:removeErrCss(this.id);" />              
            </div>
          </div>
          <div class="formInputHolder">
            <div class="left"></div>
            <div class="rightInput">
              <input type="text" name="refererEmail[]" class="right" id="email1" onFocus="javascript:removeErrCss(this.id);" />
            </div>
          </div>
          <div class="formInputHolder">
            <div class="left"></div>
            <div class="rightInput">
              <input type="text" name="refererEmail[]" class="right" id="email2" onFocus="javascript:removeErrCss(this.id);" />
            </div>
          </div>
          <div class="formInputHolder">
            <div class="left"></div>
            <div class="rightInput">
              <input type="text" name="refererEmail[]" class="right" id="email3" onFocus="javascript:removeErrCss(this.id);" />
            </div>
          </div>
          <div class="formInputHolder">
            <div class="left"></div>
            <div class="rightInput">
              <input type="text" name="refererEmail[]" class="right" id="email4" onFocus="javascript:removeErrCss(this.id);" />
            </div>
          </div>
          <div class="errorMsg" id="errorMsgShare"><div style="text-align:right;margin:0 10px 0 0;"><a href="javascript:void(0)" id="nthank">No thanks</a></div></div>
          <div class="sendButton">
            <a href="javascript:void(0);" class="sendButton"><img src="<?php echo base_url(); ?>assets/images/send.png" alt="Share"  onClick="javascript:xajax_referenceSubmit(xajax.getFormValues('prelaunchReference'));"/></a>
          </div>
        </div>
      </form>
      <div class="clearFix"></div>
    </div>
  </div>
  <!-- Reference Form Ends -->
  <!-- Thankyou Form Starts -->
  <div class="formHolder" id="messageDiv">
    <div class="innerContent">
        <div class="formInner">
          <div class="formTitle thankyou"><img src="<?php echo base_url(); ?>assets/images/thankyou.png" alt="Thank you"/></div>
          <div class="hSpacer"></div>
        </div>
      <div class="clearFix"></div>
    </div>
    <div class="clearFix"></div>
    <div class="keepyouupdated">
    	<img src="<?php echo base_url(); ?>assets/images/keepYouUpdated.png" alt="Keep you updated" />
    </div>
  </div>
  <!-- Thankyou Form Ends -->
  <!-- Pixy Kids Intro Text Starts -->
  <div class="introText">
    <div class="pixyKidsImg">
    	<img src="<?php echo base_url(); ?>assets/images/introText.png" alt="Intro" />
        </div>
  </div>
  <!-- Pixy Kids Intro Text Ends -->
  <div class="clearFix hSpacer"></div>
  	<!-- Footer -->
    <div class="footer">
        <div class="footerLogo">
            <img src="<?php echo base_url(); ?>assets/images/logoFooter.png" alt="Kazaana" />
        </div>
        <div class="links">
            <div class="copyright" style="margin:20px auto;">
                <img src="<?php echo base_url(); ?>assets/images/copyright.png" alt="Copyright Information" />
            </div>
        </div>
        <div class="clearFix"></div>
    </div>
  </div>
</div>
</body>
</html>
<script type="text/javascript">
function callEmail(){
	xajax_newsletterSubmit(xajax.getFormValues('prelaunchNewsletter'));
}

function callRefer(){
	xajax_referenceSubmit(xajax.getFormValues('prelaunchReference'));
}
function closeMe()
{
	$("#alertDialog").hide();
}
function removeErrCss(id){
	var myId = '#'+id;
	$(myId).removeClass('errorMsgTxt');
}

$(window).resize(function(){
  $('#alertDialog').css({
    position:'absolute',
    left: ($(window).width() - $('#alertDialog').outerWidth())/2,
    top: ($(window).height() - $('#alertDialog').outerHeight())/2
  });
});
$(window).resize();
</script>
