# AI-Powered Reports System Setup

## Overview
The comprehensive AI-powered reports system integrates DeepSeek Chat AI to analyze complex audit data across multiple review types, locations, and response formats. This system can handle:

- Multiple review types with location duplicates
- Various response types: text, yes/no, textarea, and table responses
- Intelligent analysis and report generation
- Location-based filtering and comprehensive data collection

## Setup Instructions

### 1. DeepSeek API Configuration
1. Sign up for a DeepSeek API account at: https://platform.deepseek.com/
2. Generate your API key from the DeepSeek dashboard
3. Add your API key to the `.env` file:
```
DEEPSEEK_API_KEY=your_actual_api_key_here
```

### 2. Permissions Setup
The reports system uses the following permissions:
- `view reports` - Can view reports dashboard and generate reports
- `generate reports` - Can generate custom reports (legacy feature)
- `export reports` - Can export reports (legacy feature)

Make sure your users have the appropriate permissions assigned.

### 3. Navigation
The new AI-powered reports are accessible via:
- **Sidebar Navigation**: Reports → AI-Powered Reports
- **Direct URL**: `/admin/reports`

## Features

### 1. Reports Dashboard (`/admin/reports`)
- View all audits with their statistics
- Quick access to generate AI reports for any audit
- DeepSeek API configuration status indicator
- Audit progress tracking

### 2. Detailed Report Generation (`/admin/reports/{audit}`)
- Location-based filtering (shows all locations for the audit's review type)
- Multiple report types:
  - **Summary Report**: Overall audit analysis and insights
  - **Detailed Analysis**: Comprehensive review of all responses
  - **Compliance Report**: Focus on compliance and recommendations
  - **Performance Report**: Performance metrics and trends
- Real-time AI report generation using DeepSeek Chat AI

### 3. Intelligent Data Processing
The system automatically:
- Collects audit data from complex relationships
- Handles duplicate review types across different locations
- Processes multiple response formats (text, yes/no, textarea, table)
- Formats data for optimal AI analysis
- Generates human-readable reports

## Data Structure Handling

### Review Types and Locations
The system handles the complex scenario where:
- Review types can have multiple locations
- Same review type appears in different locations
- Each location can have different response patterns

### Response Types
Supports all response formats:
- **Text Responses**: Short text answers
- **Yes/No Responses**: Boolean selections with optional explanations
- **Textarea Responses**: Long-form text responses
- **Table Responses**: Structured data in JSON format

## Usage Instructions

### Generating an AI Report
1. Navigate to **Reports** → **AI-Powered Reports**
2. Click "Generate Report" for the desired audit
3. Select location(s) to include in the report
4. Choose the report type
5. Click "Generate AI Report"
6. Wait for the AI analysis to complete

### Report Types Explained
- **Summary Report**: Best for executives and stakeholders
- **Detailed Analysis**: Best for audit teams and reviewers
- **Compliance Report**: Best for compliance officers
- **Performance Report**: Best for performance managers

## Technical Details

### API Integration
- Uses DeepSeek Chat AI model for intelligent analysis
- Implements proper error handling and fallbacks
- Respects API rate limits and best practices

### Data Security
- No sensitive data is stored in logs
- API communications are encrypted
- User permissions are enforced throughout

### Performance
- Efficient data collection with optimized queries
- Lazy loading for large datasets
- Caching for frequently accessed data

## Troubleshooting

### Common Issues
1. **"DeepSeek API not configured"**: Add your API key to `.env` file
2. **"No data found"**: Ensure the audit has responses to analyze
3. **"API Error"**: Check your API key and network connection

### Support
For technical support or feature requests, contact your system administrator.

## Future Enhancements
- Export reports to PDF/Excel
- Scheduled report generation
- Report templates customization
- Advanced analytics and charts
