# Propeller Challenge

This application acts as a middleware between frontend applications and an external CRM system built in Symfony 7.3 with the goals to be a RESTful API for managing subscribers, marketing lists, and enquiries.

## Requirements

- **PHP 8.3** or higher
- **Composer** (latest version)
- **Bruno** (for API testing)

## Installation

### 1. Clone the Repository

```bash
git clone <repository-url>
cd propeller_test
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Environment Configuration

Create your environment file:

```bash
cp .env .env.local
```

Edit `.env.local` with your specific configuration:

```env
APP_ENV=dev
APP_SECRET=your-secret-key

# CRM API Configuration
CRM_API_BASE_URL=https://your-crm-api.com
CRM_API_TOKEN=your-api-token
```

### 6. Start the Development Server

```bash
php -S localhost:8000 -t public/
```

The application will be available at `http://localhost:8000`

## API Endpoints

- `GET /api/lists` - Retrieve all marketing lists
- `POST /api/subscribers` - Create a new subscriber
- `POST /api/enquiries` - Create a new enquiry
- `POST /api/signup` - Complete signup process (subscriber + enquiry)

## Testing with Bruno

Bruno is a fast and Git-friendly API client used for testing the API endpoints.

### 1. Install Bruno

Download and install Bruno from: https://www.usebruno.com/

### 2. Import the Collection

1. Open Bruno
2. Click "Open Collection"
3. Navigate to the `bruno-collection` folder in the project
4. Select the folder to import the collection

### 3. Configure Environment

1. In Bruno, go to **Environments** → **Local**
2. Verify the environment variables:
   ```
   base_url: http://localhost:8000
   crm_api_base_url: https://your-crm-api.com
   crm_api_token: your-api-token
   ```

### 4. Run Test Scenarios

The collection includes organized test scenarios:

#### **Lists**
- `Get All Lists` - Retrieve all available marketing lists

#### **Subscribers**
- `Create Subscriber` - Create a new subscriber with validation

#### **Enquiries**
- `Create Enquiry` - Create an enquiry linked to a subscriber

#### **Signup**
- `Signup` - Complete signup flow with subscriber creation and enquiry
  - Automatically extracts subscriber ID for subsequent requests
  - Tests the complete user journey
