function openMenu(){document.getElementById('mobileMenu').classList.add('open')}
function closeMenu(){document.getElementById('mobileMenu').classList.remove('open')}

function toggleGdpr(wrap){
  const box=document.getElementById('gdprBox');
  const btn=document.getElementById('submitBtn');
  const checked=box.classList.toggle('checked');
  btn.disabled=!checked;
  btn.style.opacity=checked?'1':'0.4';
  btn.style.cursor=checked?'pointer':'not-allowed';
}

function toggleFaq(el){
  const i=el.parentElement,o=i.classList.contains('open');
  document.querySelectorAll('.faq-item').forEach(x=>x.classList.remove('open'));
  if(!o)i.classList.add('open');
}

function openListing(id){
  document.querySelectorAll('.modal-overlay').forEach(m=>m.classList.remove('open'));
  const modal=document.getElementById('modal-'+id);
  if(!modal)return;
  modal.classList.add('open');
  modal.scrollTop=0;
  document.body.classList.add('modal-lock');
}

function closeListing(){
  document.querySelectorAll('.modal-overlay').forEach(m=>m.classList.remove('open'));
  document.body.classList.remove('modal-lock');
}

document.addEventListener('click',function(e){
  if(e.target.classList && e.target.classList.contains('modal-overlay')){
    closeListing();
  }
});
document.addEventListener('keydown',function(e){
  if(e.key==='Escape')closeListing();
});
