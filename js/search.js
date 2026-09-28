
document.addEventListener("DOMContentLoaded",()=>{
 const input=document.getElementById("siteSearch");
 if(!input) return;
 const pages={
  member:"member.html",
  event:"events.html",
  events:"events.html",
  bulletin:"bulletin.html",
  mission:"about-us.html",
  vision:"about-us.html",
  offer:"offerings.html",
  offerings:"offerings.html",
  developer:"developer.html",
  dev:"developer.html",
  home:"index.html"
 };
 input.addEventListener("input",()=>{
  input.title=Object.keys(pages).filter(x=>x.includes(input.value.toLowerCase())).join(", ");
 });
 input.addEventListener("keydown",e=>{
  if(e.key==="Enter"){
   let k=input.value.toLowerCase();
   let match=Object.keys(pages).find(x=>x.includes(k));
   if(match) location.href=pages[match];
   else alert("No page found");
  }
 });
});
