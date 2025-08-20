const titleMap = {
  home: 'Рух капіталу',
  owner_capital: 'Капітал власника',
  operational_capital: 'Операційний капітал',
  it_company: 'ІТ компанія',
};

async function loadView(view){
  const title = document.getElementById('page-title');
  title.textContent = titleMap[view] || 'Розділ';
  const box = document.getElementById('content');
  box.innerHTML = '<div class="muted">Завантаження…</div>';
  try{
    const res = await fetch(`views/${view}.html`, {cache:'no-store'});
    if(!res.ok) throw new Error('HTTP '+res.status);
    const html = await res.text();
    box.innerHTML = html;
  }catch(e){
    box.innerHTML = '<p style="color:#ffb3b3">Не вдалося завантажити вміст.</p>';
  }
}

document.querySelectorAll('.menu-item').forEach(btn=>{
  btn.addEventListener('click',()=>{
    document.querySelectorAll('.menu-item').forEach(b=>b.classList.remove('is-active'));
    btn.classList.add('is-active');
    loadView(btn.dataset.view);
  });
});

// стартова сторінка
loadView('home');
