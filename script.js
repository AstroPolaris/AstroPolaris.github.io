// function toggleQ2(show) {
//     document.getElementById('q2').style.display = show ? 'block' : 'none';
//     document.getElementById('q2_text').required = show; // Make Q2 required only if shown
// }

// function toggleQ4() {
//     let q4 = document.getElementById('q4');
//     let disagreeSelected = false;

//     let inputs = document.querySelectorAll('input[name^="q3"]:checked');
//     inputs.forEach(input => {
//         if (input.value === "4" || input.value === "5") {
//             disagreeSelected = true;
//         }
//     });

//     q4.style.display = disagreeSelected ? 'block' : 'none';
//     document.getElementById('q4_text').required = disagreeSelected; // Make Q4 required only if shown
// }

// function validateForm() {
//     let form = document.getElementById("surveyForm");
    
//     // Check if all required fields are answered
//     let requiredInputs = form.querySelectorAll("input[required]");
//     for (let input of requiredInputs) {
//         let name = input.getAttribute("name");
//         if (!form.querySelector(`input[name="${name}"]:checked`)) {
//             alert("Please answer all required questions before submitting.");
//             return false; // Prevent form submission
//         }
//     }

//     return true; // Allow form submission
// }


document.addEventListener("DOMContentLoaded", function () {
    const q1Yes = document.querySelector("input[name='q1'][value='Yes']");
    const q1No = document.querySelector("input[name='q1'][value='No']");
    const q2Div = document.getElementById("q2");
    const q2Textarea = document.getElementById("q2_text");

    const q3Inputs = document.querySelectorAll("input[name^='q3']");
    const q4Div = document.getElementById("q4");
    const q4Textarea = document.getElementById("q4_text");

    // Toggle Q2 based on Q1 selection
    function toggleQ2() {
        let show = q1No.checked; // Show only if 'No' is selected
        q2Div.style.display = show ? "block" : "none";
        q2Textarea.required = show;
        if (!show) q2Textarea.value = ""; // Clear input if hidden
    }

    q1Yes.addEventListener("click", toggleQ2);
    q1No.addEventListener("click", toggleQ2);

    // Toggle Q4 based on Q3 selection
    function toggleQ4() {
        let showQ4 = false;
        q3Inputs.forEach(input => {
            if (input.checked && (input.value === "4" || input.value === "5")) {
                showQ4 = true;
            }
        });

        q4Div.style.display = showQ4 ? "block" : "none";
        q4Textarea.required = showQ4;
        if (!showQ4) q4Textarea.value = ""; // Clear input if hidden
    }

    q3Inputs.forEach(input => input.addEventListener("click", toggleQ4));

    // Validate form before submission
    document.getElementById("surveyForm").addEventListener("submit", function (event) {
        let requiredInputs = this.querySelectorAll("input[required], textarea[required]");
        for (let input of requiredInputs) {
            if ((input.type === "radio" || input.type === "checkbox") && !document.querySelector(`input[name="${input.name}"]:checked`)) {
                alert("Please answer all required questions before submitting.");
                event.preventDefault();
                return;
            }
            if ((input.type === "textarea" || input.type === "text") && input.required && !input.value.trim()) {
                alert("Please fill in all required text fields.");
                event.preventDefault();
                return;
            }
        }
    });
});
