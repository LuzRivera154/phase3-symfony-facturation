import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['overlay'];
    static values = { formId: String };

    open() {
        this.overlayTarget.classList.remove('hidden');
    }

    close() {
        this.overlayTarget.classList.add('hidden');
    }

    confirm() {
        document.getElementById(this.formIdValue).submit();
    }
}
