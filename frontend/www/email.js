var Email = document.emailSub.elements["MERGE0"];
Email.onfocus=function() {
    var fn = document.emailSub.elements["MERGE0"];
    if (fn.value=="Escribe tu email") fn.value="";
};
Email.onblur=function() {
    var fn = document.emailSub.elements["MERGE0"];
    if (fn.value=="") fn.value="Escribe tu email";
};