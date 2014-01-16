@extends('layouts.main')

@section('heading')
    Contact the creator
@stop

@section('content')
	If you need to get ahold of me for questions, comments, or problems, please email me here: 
    
    <script type="text/javascript" language="javascript">
<!--
// Email obfuscator script 2.1 by Tim Williams, University of Arizona
// Random encryption key feature by Andrew Moulden, Site Engineering Ltd
// This code is freeware provided these four comment lines remain intact
// A wizard to generate this code is at http://www.jottings.com/obfuscator/
{ coded = "02NQQPzNQQP@zTnyQ.eNT"
  key = "N6LVPeUJ2kM0htwjHEI7RqdliGmOapBuDgYf93K4rovAQycTbnWXs1zxCF5Z8S"
  shift=coded.length
  link=""
  for (i=0; i<coded.length; i++) {
    if (key.indexOf(coded.charAt(i))==-1) {
      ltr = coded.charAt(i)
      link += (ltr)
    }
    else {     
      ltr = (key.indexOf(coded.charAt(i))-shift+key.length) % key.length
      link += (key.charAt(ltr))
    }
  }
document.write("<a href='mailto:"+link+"'>"+link+"</a>")
}
//-->
</script><noscript>Sorry, you need Javascript on to email me.</noscript> 
<br>
<a href="https://twitter.com/SoMazeWow" class="twitter-follow-button" data-show-count="false" data-size="large" data-dnt="true">Follow @SoMazeWow</a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
<br><hr>
<h2>Donations:</h2><br>
Hi, my name is SLoW.  I've worked really hard on this game, and I hope you're really enjoying it at this point.  Maybe you've solved some puzzles, maybe you've even created some puzzles.  You've probably noticed that when you create a puzzle, I charge a small "creation fee".  The purpose of this fee isn't to get rich, it's mainly just to offset the cost of hosting, and hopefully development.  If you've been enjoying the game and want to show your appreciation, I have addresses below for donations.  I would be forever grateful for them, but they are completely optional.<br><br>

    <ul>
    <li>BTC: 1FVHqRocEwETo1qcsTijzCQZkcFN26ssQn</li>
    <li>LTC: LhkiPoj8oz8uexirUdvk85XW22sK44iXNB</li>
    <li>DOGE: D8JXjDNPmUuPamDkveTZ8SvnvNeHoZEsFg</li>
    </ul>
    <br>

Thank you for your time and your support!
@stop