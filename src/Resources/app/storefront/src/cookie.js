import { COOKIE_CONFIGURATION_UPDATE } from 'src/plugin/cookie/cookie-configuration.plugin';

const cookieName = 'NuonicSearchHub-js';

if (document.cookie.split(';').some((item) => item.trim().includes(cookieName + '=1'))) {
  runSearchHubScripts();
}

document.$emitter.subscribe(COOKIE_CONFIGURATION_UPDATE, function (updatedCookies) {
  if (typeof updatedCookies.detail[cookieName] !== 'undefined') {
    let cookieActive = updatedCookies.detail[cookieName];
    if (cookieActive) {
      runSearchHubScripts();
    }
  }
});
