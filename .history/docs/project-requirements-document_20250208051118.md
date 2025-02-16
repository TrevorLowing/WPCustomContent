# Project Requirements Document

## Plugin Name: PiperPerks

### Objective:
Develop a WordPress plugin focused on **Referral Marketing** and **Social Sharing Incentives Marketing**. The plugin encourages website visitors to perform specific tasks (e.g., sharing content, referring users, contributing to projects) in exchange for rewards. It integrates seamlessly with WordPress and includes optional FluentCRM functionality for enhancing referral campaigns and email automation.

----

## 1. General Requirements

1. **Custom WordPress Menu:**
   - Add a custom menu in the WordPress Admin Dashboard titled **"Get Rewards"**.
   - Submenu options:
     - Dashboard (high-level stats)
     - Tasks Management
     - FluentCRM Integration
     - Reward Types Configuration

2. **Requirements Tracker:**
   - Maintain and update a progress tracker file named `requirements.md` in the project’s `/docs/` folder.
   - Track the status for each task as one of the following:
     - **Not Started**
     - **In Progress**
     - **Completed**

3. **UI Design Integration:**
   - Refer to the preexisting **default UI screenshots stored in the `/docs/` folder** for the plugin's frontend and backend UI. Replicate these designs in the plugin where applicable.
   - Create a modern, easy-to-navigate interface for both:
     - Admin dashboard tasks and rewards configuration.
     - User-facing frontend where users can complete tasks and view their rewards.

4. **WordPress Standards Compliance:**
   - Follow WordPress coding standards.
   - Use hooks, filters, and APIs for clean and secure integration.
   - Ensure compatibility with popular themes and plugins.

----

### 1.5. Accessibility
- Ensure the plugin is accessible to all users, including those with disabilities.
- Follow the Web Content Accessibility Guidelines (WCAG) 2.1.
- Implement features such as:
  - Keyboard navigation support.
  - Screen reader compatibility.
  - High contrast mode for better visibility.

### 1.6. Language Support
- Provide support for multiple languages.
- Use WordPress internationalization functions to make the plugin translatable.
- Include a default set of translations for common languages (e.g., Spanish, French, German).
- Allow admins to add custom translations via .po and .mo files.

### 1.7. Performance Optimization
- Ensure the plugin is optimized for performance.
- Minimize the use of external scripts and styles.
- Implement caching mechanisms where applicable.
- Test the plugin for performance on various hosting environments.

### 1.8. Security Measures
- Follow WordPress security best practices.
- Sanitize and validate all user inputs.
- Use nonces for form security.
- Regularly update dependencies to patch known vulnerabilities.

### 1.9. Testing and Quality Assurance
- Implement unit tests for all major functionalities.
- Conduct integration tests to ensure seamless interaction between different components.
- Perform user acceptance testing (UAT) with a group of beta testers.
- Ensure cross-browser compatibility and responsiveness.
- Document all test cases and results.

----

## 2. Functional Requirements

### 2.1. GitHub Star Reward
- Feature allowing users to **submit their GitHub username**.
- Ask users to star a designated GitHub repository via the GitHub API.
- Validate if the repository has been starred:
  - If validated, mark the user as eligible for the reward (saved in their WordPress user metadata).
  - Provide a **higher-tier reward** for meaningful contributions to the repository (e.g., accepted pull requests or documentation updates). *Do not reward solely for submitting issues.*

----

### 2.2. Social Network Sharing Reward
- Admins can:
  1. Enable users to **submit their social network username** (e.g., Twitter, Instagram, Facebook, LinkedIn).
  2. Configure a task for users to **share posts about the website or product** (e.g., links, hashtags).
  3. Select one or multiple social platforms from a **dropdown selector** in the admin interface.
  4. Set reward tiers:
     - **Basic Reward:** Completing a social media post or link share.
     - **Higher Reward:** Engagement metrics (likes, reposts, or mentions).

- Validate shared social posts programmatically or via link/human verification:
  - Save the validation status to the user's account metadata.
  - Display a completion status ("Incomplete," "Pending Validation," or "Completed").

----

### 2.3. Referral Reward System
- Users can refer friends by providing their **email address**.
- Send an **automated email invitation** to the referred user with an embedded referral link.
- Define two reward tiers:
  1. **Tier 1 Reward:** Referred user clicks the embedded link.
  2. **Tier 2 Reward:** Referred user signs up as a WordPress member via the referral link.
- Track referral status:
  - Emails sent and opened.
  - Referral links clicked.
  - User registrations attributable to specific referrals.

----

### 2.4. FluentCRM Integration
- **FluentCRM Integration Options:**
  1. Automatically create/update FluentCRM contacts when:
     - A user submits a referral email.
     - A referred user clicks the referral link or signs up as a member.
  2. Add custom tags to contacts (e.g., "Referred-User," "Referrer").
  3. Track referral data in FluentCRM custom fields.

- **Email Campaign Management:**
  1. Automatically trigger FluentCRM campaigns for referrals:
     - **Initial Referral Email**  
       *(Template provided in Section 4).*
     - **Follow-up Reminder**  
       *(Template provided in Section 4).*
     - **Thank You Email**  
       *(Template provided in Section 4).*
  2. Allow admins to configure workflows like:
     - "New Referral Contact" automation.
     - "Referral Completed" automation.
     - "Reward Confirmation" email sequences.
  3. Utilize FluentCRM's **free features** to:
     - Send email sequences.
     - Track referrals in the FluentCRM analytics dashboard.
     - Manage custom fields for referral workflows.

- Provide fallback mechanism:
  - Clearly notify admins if FluentCRM is not installed, activated, or configured.

----

### 2.5. User Account Management
- **User Profile Page:**
  - Create a dedicated user profile page where users can view and manage their rewards and tasks.
  - Display user-specific information such as completed tasks, earned rewards, and referral status.
  - Allow users to update their profile information (e.g., social media usernames, email address).

- **Account Security:**
  - Implement security measures to protect user data.
  - Ensure secure authentication and authorization mechanisms.
  - Provide options for users to reset their passwords and manage account recovery.

- **Notification System:**
  - Enable users to opt-in for notifications related to task completions, reward eligibility, and referral status.
  - Allow users to customize their notification preferences (e.g., email, SMS).

- **Data Privacy:**
  - Ensure compliance with data privacy regulations (e.g., GDPR).
  - Provide users with options to download their data and request account deletion.

----

### 2.6. Analytics and Reporting
- Provide detailed analytics and reporting for admins.
- Track user engagement metrics such as task completions, referral clicks, and reward redemptions.
- Display analytics in a user-friendly dashboard within the WordPress admin area.
- Allow exporting of reports in CSV format.

----

## 3. Admin-Side Reward Management
- **Reward Type Management:**
  1. Display a default set of reward suggestions:
     - Discount codes.
     - Free products/services.
     - Tiered loyalty points.
     - Exclusive perks (e.g., downloadable content).
  2. Allow admins to:
     - Add/modify custom reward types.
     - Assign rewards to tasks based on completion criteria.
  3. Include a **reward history log** for tracking:

     | **Reward Type** | **User Name** | **Date**           | **Status**         |
     |------------------|---------------|--------------------|--------------------|
     | Free Download    | john_doe      | 02/11/2025         | Completed          |
     | Discount Code    | jane_smith    | 02/12/2025         | Pending Validation |

----

## 4. Predefined FluentCRM Email Templates

### Initial Referral Email Template:
```html
Subject: {{referrer_name}} thinks you'll love {{site_name}}!

Hi {{first_name}},

Your friend, {{referrer_name}}, invited you to check out {{site_name}}! As a bonus, you'll receive {{special_offer}} if you join.  

Click below to get started:  
{{referral_link}}

Cheers,  
The {{site_name}} Team
```

### Follow-up Reminder Email Template:
```html
Subject: Reminder: Join {{site_name}} and claim your reward!

Hi {{first_name}},

We noticed you haven't joined {{site_name}} yet. Don't miss out on the special offer from {{referrer_name}}.  

Click below to join now:  
{{referral_link}}

Best,  
The {{site_name}} Team
```

### Thank You Email Template:
```html
Subject: Thank you for joining {{site_name}}!

Hi {{first_name}},

Thank you for joining {{site_name}}! We hope you enjoy your experience.  

As a token of our appreciation, here is your special reward: {{reward_details}}  

Best regards,  
The {{site_name}} Team
```

### Reward Confirmation Email Template:
```html
Subject: Your reward from {{site_name}} is confirmed!

Hi {{first_name}},

Congratulations! Your reward for completing the task on {{site_name}} has been confirmed.  

Reward Details: {{reward_details}}  

Thank you for your participation!  

Best,  
The {{site_name}} Team
```

### New Referral Contact Automation Email Template:
```html
Subject: New Referral Contact Added

Hi Admin,

A new referral contact has been added to FluentCRM.  

Referrer: {{referrer_name}}  
Referred User: {{referred_user_name}}  

Best,  
The {{site_name}} Team
```

### Referral Completed Automation Email Template:
```html
Subject: Referral Completed Successfully

Hi Admin,

A referral has been successfully completed.  

Referrer: {{referrer_name}}  
Referred User: {{referred_user_name}}  

Best,  
The {{site_name}} Team
```

### Reward Confirmation Email Sequence Template:
```html
Subject: Reward Confirmation for {{site_name}}

Hi {{first_name}},

Your reward for participating in {{site_name}} has been confirmed.  

Reward Details: {{reward_details}}  

Thank you for your engagement!  

Best regards,  
The {{site_name}} Team
