class MainObject {
    constructor() {
        this.isReactDev = window.location.origin === 'http://localhost:3000';
        this.ajaxUrl = this.isReactDev ? 'https://jsonplaceholder.typicode.com/' :
            this.getGlobalProperty('ajaxUrl', '');
        this.nonce = this.isReactDev ? '' : this.getGlobalProperty('nonce', '');
        this.title = this.getGlobalProperty('title', 'User Atlas');
        this.isUserAdmin = this.getGlobalProperty('isUserAdmin', false);
    }

    getGlobalProperty(property, defaultValue = '') {
        return this.isReactDev ? defaultValue : window?.global_user_atlas?.[property] || defaultValue;
    }

    usersListUrl() {
        const queryParams = this.isReactDev
            ? 'users/'
            : `?action=user_atlas&nonce=${this.nonce}&cmd=users`;

        return this.ajaxUrl + queryParams;
    }

    userDetailsUrl(id) {
        if (!id) {
            throw new Error('User ID is required');
        }

        const queryParams = this.isReactDev
            ? 'users/' + id
            : `?action=user_atlas&nonce=${this.nonce}&cmd=user&id=${id}`;

        return this.ajaxUrl + queryParams;
    }
}

export default new MainObject();
