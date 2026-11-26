// Cookie banner: remember the visitor's choice for one year.
document.querySelectorAll('[data-cookie]').forEach(function (button) {
  button.addEventListener('click', function () {
    document.cookie = 'cookie_choice=' + button.dataset.cookie + '; max-age=31536000; path=/; SameSite=Lax';
    document.getElementById('cookieBanner').remove();
  });
});

var resetCookie = document.getElementById('resetCookie');
if (resetCookie) {
  resetCookie.addEventListener('click', function () {
    document.cookie = 'cookie_choice=; max-age=0; path=/';
    location.reload();
  });
}

// Payment page: show the box for the chosen payment method.
function showPaymentBox() {
  var chosen = document.querySelector('input[name="payment"]:checked');
  document.querySelectorAll('.paypanel').forEach(function (box) {
    box.hidden = !chosen || box.dataset.method !== chosen.value;
  });
}
document.querySelectorAll('input[name="payment"]').forEach(function (radio) {
  radio.addEventListener('change', showPaymentBox);
});
showPaymentBox();

// Sign-in page: fill in a ready-made account.
document.querySelectorAll('[data-email]').forEach(function (button) {
  button.addEventListener('click', function () {
    document.getElementById('email').value = button.dataset.email;
    document.getElementById('password').value = button.dataset.password;
  });
});
