CCMS :   The Commercial Client Management System (CCMS) is designed to streamline and automate the data collection, client management, and payment tracking processes for the commercial department of an architecture company. The platform aims to reduce manual effort, eliminate data duplication, and improve client relationship management. By leveraging modern technologies, CCMS will provide a comprehensive solution for tracking client interactions, managing client statuses, and ensuring efficient payment processing.

# BizServe as a Solution 🚀
The BizServe project involves developing a web application for the commercial service of an architecture company offering courses and interior design services.
The application aims to streamline the collection and management of potential client data, track client interactions and offers, and monitor payment statuses.
By utilizing web development and design technologies, the BizServe project will provide a robust and efficient client management system tailored to the needs of the commercial service. This approach ensures scalability, security, and a superior user experience, aligned with the project's objectives.

# The use of JWT 💡
The following sequence diagram describes the authentication process of a client using JWT technology.  
The client sends a request to access the authentication page and then enters their credentials to log into their dashboard. The API first verifies the credentials, and if everything is correct, the client receives a token. This token is then validated to grant access to the client's information, which will be displayed in their profile section.  
However, if the credentials are incorrect or the token is invalid, an error message is displayed on the authentication page, informing the client about the issue encountered during request processing.

![JWT](/assets/jwt.png)

# Creating APIs 🛠️
To ensure the efficient implementation of the APIs, we used several useful documents provided by our supervisor during the project. We also used Postman to verify the functionality and review the results of the sent requests.  
After this step, we created a workspace on Postman that contains all the different APIs, well-organized.

![APIs](/assets/project-apis.png)

and here you can see the group of endpoints in clients API : <br><br>
![Clients](/assets/clients-api.png)

# Backend with laravel 🤝
Laravel is a widely used server-side PHP framework for developing robust and scalable web applications. It provides a well-structured architecture and practical tools to simplify the development process.

### Role in the Project:
Laravel was used to develop the backend logic of the BizServe API. It facilitated managing operations related to the offered services, including creating, updating, deleting, and retrieving data. Additionally, Laravel enabled user authentication using JWT and role management.

### ✨ Another Repository involves the front-end part of the project ✨ 
