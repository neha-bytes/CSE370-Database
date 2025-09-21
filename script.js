document.getElementById('phone').addEventListener('input', function () {
    this.value = this.value.replace(/[^0-9]/g, ''); 
});

document.querySelectorAll('.user-type-specific').forEach(field => {
    field.style.display = 'none';
});

function toggleUserSpecificFields(userType) {
    document.querySelectorAll('.user-type-specific').forEach(field => {
        field.style.display = 'none';
        field.querySelectorAll('input').forEach(input => input.required = false);
    });
    if (userType) {
        const specificFields = document.getElementById(`${userType}Fields`);
        if (specificFields) {
            specificFields.style.display = 'block';
            specificFields.querySelectorAll('input').forEach(input => input.required = true);
        }
    }
}

function validateMinimumValue(input) {
    if (input.value && input.value < 1) {
        input.value = ''; 
    }
}

['donationAmount', 'total', 'st'].forEach(id => {
    const element = document.getElementById(id);
    if (element) {
        element.addEventListener('input', function () {
            validateMinimumValue(this);
        });
    }
});

const signUpButton=document.getElementById('signUpButton');
const signInButton=document.getElementById('signInButton');
const signInForm=document.getElementById('signIn');
const signUpForm=document.getElementById('signup');

signUpButton.addEventListener('click',function(){
    signInForm.style.display="none";
    signUpForm.style.display="block";
})
signInButton.addEventListener('click', function(){
    signInForm.style.display="block";
    signUpForm.style.display="none";
})