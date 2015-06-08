(function($){
var deck = {

	notesFormWrapper: $('.nd-notes-wrapper'),
	notesForm: $('.nd-notes-form'),
	notesFormText: $('.nd-notes-content'),
	notesFormEmail: $('.nd-notes-email'),
	notesFormButton: $('.nd-notes-button'),
	notesCloseButton: $('.nd-notes-close'),
	notesSendButton: $('.nd-notes-send'),
	addNotesButton: $('.add-notes-button'),
	formActive: false,

	init: function() {

		// Sets up the event handler for the notes button
		deck.notesFormButton.on('click', function() {
			deck.toggleForm();
		});
		deck.notesCloseButton.on('click', function() {
			deck.toggleForm();
		});

		// Set up the click handler for the send button
		deck.notesForm.on('submit', function(e) {
			e.preventDefault();
			deck.submitNotesForm( $(this) );
		});

		deck.addNotesButton.on('click', function() {
			var button = $(this);
			deck.addNotes(button);
		});


	},

	addNotes: function(button) {
		var content = '\n \n' + button.siblings('.nd-text-content').text().trim() + '\n ----------------------------------------------------------- \n';
		deck.notesFormText.val(deck.notesFormText.val() + content);

		deck.notesFormText.scrollTop(deck.notesFormText[0].scrollHeight);

		button.html('NOTES COPIED');
		setTimeout(function() {
			button.html('<span class="glyphicon glyphicon-plus" aria-hidden="true"></span> COPY TO NOTES');
		}, 3000);
	},

	toggleForm: function() {
		deck.removeErrors();
		if ( deck.formActive ) {
			deck.notesFormWrapper.fadeOut();

			$('body, html').removeClass('no-scroll');
		} else {
			var textArea = deck.notesFormText;
			deck.notesFormWrapper.fadeIn();
			$('body, html').addClass('no-scroll');
			deck.notesFormText.scrollTop(deck.notesFormText[0].scrollHeight);
		}

		deck.formActive = ! deck.formActive;
	},

	preventTouchScrolling: function(e) {
		if (e.target.attr('class') !== 'nd-notes-content') {
	        e.preventDefault();
	    }
	},

	removeErrors: function() {
		deck.notesFormEmail.removeClass('error');
		deck.notesFormText.removeClass('error');
	},

	submitNotesForm: function() {
		deck.removeErrors();

		var email = deck.notesFormEmail.val();
		var notes = deck.notesFormText.val();
		var error = false;

		if (email === '') {
			deck.notesFormEmail.addClass('error');
			error = true;
		}
		if (notes === '') {
			deck.notesFormText.addClass('error');
			error = true;
		}
		if (error) {
			return;
		}

		deck.notesSendButton.prop('disabled', true);
		deck.notesSendButton.val('SENDING...');

		$.ajax({
 			method: "POST",
 			url: "/wp-content/plugins/notedeck/ajax/email_handler.php",
 			data: { 
 				email: email, 
 				content: notes
			}
		}).done( function(returnData) {
			deck.handleSubmitedForm(returnData);
		});
	},

	handleSubmitedForm: function(returnData) {
		if (returnData) {
			deck.toggleForm();
		} else {
			deck.notesForm.before('<div class="email-failed alert alert-danger" role="alert"><strong>WARNING!</strong> An error occured while trying to send the email!</div>');
			var notification = deck.notesFormWrapper.find('.email-failed').hide().slideDown();
			setTimeout(function(){
				notification.slideUp('fast', function() {
					notification.remove();
				});
			}, 3000);
		}
		deck.notesSendButton.prop('disabled', false);
		deck.notesSendButton.val('SEND');
	}
};

deck.init();

})(jQuery);