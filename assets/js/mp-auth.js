
// console.log(mpAuth)



jQuery($ => {
	const menu = $('.mp-auth-menu')

	const appRoot = sessionStorage.getItem('appRoot')

	window.addEventListener('load', () => {
		fetch( `${appRoot}/Api/Auth/User`, {
			headers: {
				"Authorization": `Bearer ${localStorage.getItem('mpp-widgets_AuthToken')}`,
			}
		} )
		.then(res => createMenu(res.status === 200))
		.catch(() => createMenu(false))
	})

	const getLoginUrl = async () => {
		try {
			const oauthConfig = await fetch(`${appRoot}/Api/Auth`).then(res => res.json())
			const url = "".concat(oauthConfig.signInUrl, "?") + "response_type=".concat(oauthConfig.responseType) + "&scope=".concat(oauthConfig.scope) + "&client_id=".concat(oauthConfig.clientId) + "&redirect_uri=".concat(oauthConfig.redirectUrl) + "&nonce=".concat(oauthConfig.nonce) + "&state=".concat(encodeURIComponent(window.location));
			return url
		}
		catch(err) {
			console.error("Error getting login url", err)
			return '/account';
		}
	}

	function createMenu(loggedIn = false) {
		const items = loggedIn ? mpAuth.loggedInMenu : mpAuth.loggedOutMenu
		
		items.forEach(async item => {
			const $menuItem = $('<li>')

			const $link = $('<a>')
			$link.attr('class', 'mp-auth-link')
			$link.text(item.text)

			if(item.login) {
				const loginUrl = await getLoginUrl()
				$link.attr('href', loginUrl)
			}
			else {
				$link.attr('href', item.url)	
			}

			$menuItem.append($link)
			menu.append($menuItem)
		})
	}
});

// check if on the /account page with the ?action=logout query string
// if so, remove any auth tokens from local storage
(function() {
	if(window.location.pathname !== '/account/') return

	const urlParams = new URLSearchParams(window.location.search)
	if (urlParams.get('action') === 'logout') {
		localStorage.removeItem('mpp-widgets_AuthToken')
		localStorage.removeItem('mpp-widgets-IdToken')
		localStorage.removeItem('mpp-widgets-ExpiresAfter')
	}
})();