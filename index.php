<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Database;
use App\Response;
use App\Router;

// Initialize router
Router::init();

// CORS headers for all requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(200);
  exit();
}

// Set error handler
set_error_handler(function($severity, $message, $file, $line) {
  if (error_reporting() & $severity) {
    Response::serverError("Server error: {$message}");
  }
});

// Set exception handler
set_exception_handler(function($exception) {
  Response::serverError("Server error: " . $exception->getMessage());
});

// Routes
// Home route
Router::get('/', function() {
  Response::success([
    'message' => 'Welcome to Modern PHP API',
    'version' => '1.0.0',
    'endpoints' => [
      'GET /' => 'This welcome message',
      'GET /api/health' => 'Health check',
      'GET /api/users' => 'Get all users',
      'GET /api/users/{id}' => 'Get user by ID',
      'POST /api/users' => 'Create new user',
      'PUT /api/users/{id}' => 'Update user',
      'DELETE /api/users/{id}' => 'Delete user',
      'GET /api/posts' => 'Get all posts with user info'
    ],
    'example_payload' => [
      'name' => 'John Doe',
      'email' => 'john@example.com'
    ]
  ]);
});

// Health check
Router::get('/api/health', function() {
  try {
    Database::query("SELECT 1");
    Response::success([
      'status' => 'healthy',
      'database' => 'connected',
      'timestamp' => date('Y-m-d H:i:s')
    ]);
  } catch (Exception $e) {
    Response::error('Database connection failed', 500, [
      'database' => 'disconnected',
      'error' => $e->getMessage()
    ]);
  }
});

// API status
Router::get('/api', function() {
  Response::success([
    'api_status' => 'OK',
    'timestamp' => date('Y-m-d H:i:s'),
    'version' => '1.0.0'
  ]);
});

// Include API route files
require_once __DIR__ . '/api/user.php';

// Run the router
Router::run();