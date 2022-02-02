import {MDCTextField} from '@material/textfield';
import {MDCRipple} from '@material/ripple';

/*
|-------------------------------------------------------------
| Init fields.
|-------------------------------------------------------------
*/

const fields = document.getElementsByClassName('mdc-text-field');
for(let field of fields) {
    new MDCTextField(field);
}

/*
|-------------------------------------------------------------
| Init buttons.
|-------------------------------------------------------------
*/

const buttons = document.getElementsByClassName('mdc-button');
for(let button of buttons) {
    new MDCRipple(button);
}
