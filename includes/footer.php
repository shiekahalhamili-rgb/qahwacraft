<?php
// Bottom of every page: footer and cookie banner.
?>
</main>

<footer class="site">
  <div class="wrap">
    <div style="display:flex;flex-direction:column;gap:10px">
      <span class="brand-name">Qahwa <b>Craft</b></span>
      <p class="muted">Beans from Saudi micro-roasters and the tools to brew them, in one cart and one box.</p>
      <div class="paylogos"><span class="paylogo">mada</span><span class="paylogo">Apple Pay</span></div>
      <p class="muted" style="font-size:13px">Instagram · TikTok · Snapchat: @qahwacraft (planned)</p>
    </div>
    <div><h4>Shop</h4><ul>
      <li><a href="shop.php?type=beans">Coffee beans</a></li>
      <li><a href="shop.php?type=tool">Brewing tools</a></li>
      <li><a href="shop.php?type=accessory">Accessories</a></li>
      <li><a href="subscribe.php">Subscriptions</a></li>
    </ul></div>
    <div><h4>Help</h4><ul>
      <li><a href="track.php">Track your order</a></li>
      <li><a href="page.php?p=faq">FAQ</a></li>
      <li><a href="contact.php">Contact us</a></li>
      <li><a href="page.php?p=returns">Returns &amp; refunds</a></li>
      <li><a href="page.php?p=shipping">Shipping</a></li>
    </ul></div>
    <div><h4>About</h4><ul>
      <li><a href="page.php?p=about">About us &amp; team</a></li>
      <li><a href="page.php?p=privacy">Privacy policy</a></li>
      <li><a href="page.php?p=terms">Terms &amp; conditions</a></li>
      <li><a href="page.php?p=cookies">Cookie policy</a></li>
    </ul></div>
    <div class="foot-note"><?= content('footer-note') ?></div>
  </div>
</footer>

<?php if (empty($_COOKIE['cookie_choice'])): ?>
<div class="cookie" id="cookieBanner">
  <div class="wrap">
    <p>We use essential cookies for your cart and sign-in. With your permission we also use analytics cookies. <a href="page.php?p=cookies">Cookie policy</a></p>
    <div class="acts">
      <button class="btn sm" type="button" data-cookie="all">Accept all</button>
      <button class="btn ghost sm" type="button" data-cookie="essential">Essential only</button>
    </div>
  </div>
</div>
<?php endif; ?>

<script src="assets/script.js"></script>
</body>
</html>
