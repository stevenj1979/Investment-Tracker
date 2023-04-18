document.addEventListener(‘Click’, e => {
	const isDropdownButton = e.target.matches(“[data-dropdown-button”)
	if (!isDropdownButton && e.target.closest(‘[data-dropdown]’) != null) return

	let currentDropdown
	if (isDropdownButton) {
		currentDropdown = e.target.closest(‘[data-dropdown]’)
		currentDropdown.classlist.toggle(‘active’)
	}

	document.querySelectorAll(“[data-dropdown].active”).foreach(dropdown => {
		if (dropdown === currentDropdown) return
		dropdown.classlist.remove(“active”)
	})
})
