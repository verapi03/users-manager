# users-manager
Functional basic user management system with sign-up, sign-in and user management.

The app uses the MVC architectural pattern developed entirely in CakePHP 2.8. The basic structure of the project is the following:

The controller UsersController communicates with the model User to process data from and for users or with SocialProfile to handle facebook login requests, after this communication is done, the controller delegates to a sepecific view object the task of generating output for the client. This project is based on conventions over configuration, so the controller was named UsersController, the model was named User and the views are named after the UsersController's methods (create, edit, etc...).
