
// console.log(mpAuth)

jQuery($ => {
	const menu = $('.mp-auth-menu')

	const appRoot = sessionStorage.getItem('appRoot')

	fetch( `${appRoot}/Api/Auth/User` )
	.then(res => {
		if(res.status === 200 || res.status === 204) {
			createMenu(true)
		}
		else {
			createMenu(false)
		}
	})
	.catch(() => {
		createMenu(false)
	})

	function createMenu(loggedIn = false) {
		const items = loggedIn ? mpAuth.loggedInMenu : mpAuth.loggedOutMenu
		
		items.forEach(item => {
			const li = $('<li>')
			const a = $('<a>')
			a.attr('href', item.href)
			a.text(item.text)
			li.append(a)
			menu.append(li)
		})
	}
})