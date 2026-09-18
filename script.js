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

function showContactTab(tab){
  const panelInfo=document.getElementById('panelInfo');
  const panelForm=document.getElementById('panelForm');
  const tabInfo=document.getElementById('tabInfo');
  const tabForm=document.getElementById('tabForm');
  if(!panelInfo||!panelForm)return;
  panelInfo.hidden = tab!=='info';
  panelForm.hidden = tab!=='form';
  tabInfo.classList.toggle('active', tab==='info');
  tabForm.classList.toggle('active', tab==='form');
}

let lightboxImages=[];
let lightboxIndex=0;

function openLightbox(listingId, index){
  const imgs=document.querySelectorAll('#modal-'+listingId+' .lightbox-src');
  lightboxImages=Array.from(imgs).map(img=>({src:img.getAttribute('src'), alt:img.getAttribute('alt')||''}));
  lightboxIndex=index;
  renderLightbox();
  document.getElementById('lightbox').classList.add('open');
}

function renderLightbox(){
  const item=lightboxImages[lightboxIndex];
  if(!item)return;
  const img=document.getElementById('lightboxImg');
  img.src=item.src;
  img.alt=item.alt;
  document.getElementById('lightboxCount').textContent=(lightboxIndex+1)+' / '+lightboxImages.length;
}

function lightboxNext(e){
  if(e)e.stopPropagation();
  lightboxIndex=(lightboxIndex+1)%lightboxImages.length;
  renderLightbox();
}

function lightboxPrev(e){
  if(e)e.stopPropagation();
  lightboxIndex=(lightboxIndex-1+lightboxImages.length)%lightboxImages.length;
  renderLightbox();
}

function closeLightbox(){
  document.getElementById('lightbox').classList.remove('open');
}

document.addEventListener('click',function(e){
  if(e.target.id==='lightbox'){
    closeLightbox();
  }
});

document.addEventListener('keydown',function(e){
  if(e.key==='Escape'){
    closeLightbox();
    closeListing();
  }
  const lb=document.getElementById('lightbox');
  if(lb && lb.classList.contains('open')){
    if(e.key==='ArrowRight')lightboxNext();
    if(e.key==='ArrowLeft')lightboxPrev();
  }
});
