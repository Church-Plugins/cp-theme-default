
// console.log(mpAuth)

jQuery($ => {
	const menu = $('.mp-auth-menu')

	const appRoot = sessionStorage.getItem('appRoot')

	fetch( `${appRoot}/Api/Auth/User`, {
		headers: {
			"Authorization": `Bearer ${localStorage.getItem('mpp-widgets_AuthToken')}`,
		}
	} )
	.then(res => {
		createMenu(res.status === 200)
	})
	.catch(() => {
		createMenu(false)
	})

	function createMenu(loggedIn = false) {
		const items = loggedIn ? mpAuth.loggedInMenu : mpAuth.loggedOutMenu
		
		items.forEach(item => {
			const li = $('<li>')
			const a = $('<a>')
			a.attr('class', 'mp-auth-link')
			a.attr('href', item.url)
			a.text(item.text)
			li.append(a)
			menu.append(li)
		})
	}
});

// check if on the /account page with the ?action=logout query string
// if so, remove any auth tokens from local storage
(function() {
	if(window.location.pathname !== '/account') return

	const urlParams = new URLSearchParams(window.location.search)
	if (urlParams.get('action') === 'logout') {
		localStorage.removeItem('mpp-widgets_AuthToken')
		localStorage.removeItem('mpp-widgets-IdToken')
		localStorage.removeItem('mpp-widgets-ExpiresAfter')
	}
})();