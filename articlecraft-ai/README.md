# ArticleCraft AI - WordPress Plugin

A modern WordPress plugin that uses AI to create and rewrite blog articles with professional journalism standards. Generate high-quality content from URLs or enhance existing articles with AI-powered rewriting.

## 🚀 Features

- **URL-to-Article Generation**: Paste any URL and let AI create a professional article based on the content
- **Content Rewriting**: Enhance existing articles with AI-powered rewriting
- **Multiple AI Providers**: Support for OpenAI GPT, Anthropic Claude, and Google Gemini
- **Professional Journalism Standards**: Built-in custom instructions for high-quality, human-like content
- **SEO Optimization**: Automatic generation of meta titles and descriptions
- **Gutenberg Integration**: Seamless integration with WordPress block editor
- **Activity Logging**: Track all AI operations and token usage
- **Real-time Processing**: Live progress indicators for AI operations

## 📋 Requirements

- WordPress 5.8 or higher
- PHP 7.4 or higher
- One of the following AI provider API keys:
  - OpenAI API key
  - Anthropic Claude API key
  - Google Gemini API key

## 🔧 Installation

### Method 1: Manual Installation

1. Download the plugin files
2. Upload the `articlecraft-ai` folder to your `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Configure your API keys in the settings

### Method 2: Upload via WordPress Admin

1. Go to **Plugins > Add New > Upload Plugin**
2. Choose the plugin ZIP file
3. Click **Install Now** and then **Activate**

## ⚙️ Configuration

### 1. API Setup

After activation, go to **Settings > ArticleCraft AI** to configure:

1. **AI Provider**: Choose between OpenAI, Claude, or Gemini
2. **API Key**: Enter your chosen provider's API key
3. **Model Settings**: Configure model, tokens, and temperature

### 2. Getting API Keys

#### OpenAI (Recommended)
- Visit [OpenAI Platform](https://platform.openai.com/api-keys)
- Create account and generate API key
- Models: GPT-3.5 Turbo, GPT-4, GPT-4 Turbo

#### Anthropic Claude
- Visit [Anthropic Console](https://console.anthropic.com/)
- Create account and generate API key
- Models: Claude 3 Sonnet, Claude 3 Opus

#### Google Gemini
- Visit [Google AI Studio](https://makersuite.google.com/app/apikey)
- Create account and generate API key
- Models: Gemini Pro

## 📝 Usage

### In Post Editor

The plugin adds an **ArticleCraft AI Generator** meta box to your post editor with two main functions:

#### 1. Generate from URL
1. Paste any URL in the input field
2. Click **Generate Article**
3. AI will analyze the URL content and create a new article
4. Generated content includes:
   - Professional article title
   - High-quality content following journalism standards
   - SEO-optimized meta title (under 54 characters)
   - Meta description (120-155 characters)

#### 2. Rewrite Current Content
1. Add your existing content to the editor
2. Click **Rewrite Article**
3. AI will enhance and rewrite your content
4. Maintains your original message while improving:
   - Structure and flow
   - Readability and engagement
   - Professional tone
   - SEO optimization

### Admin Menu Locations

After installation, you'll find ArticleCraft AI in these locations:

1. **Settings Menu**: `Settings > ArticleCraft AI`
   - Configure API keys and model settings
   
2. **Tools Menu**: `Tools > ArticleCraft AI Logs`
   - View processing history and token usage

3. **Post Editor**: Look for the **ArticleCraft AI Generator** meta box on the right sidebar when editing posts

## 🎯 Content Quality Features

The plugin uses advanced custom instructions to ensure:

- **Professional journalism standards**
- **Engaging, human-like writing**
- **Avoids AI-generated clichés and phrases**
- **Proper article structure with headings**
- **Strong opening hooks and impactful conclusions**
- **Varied sentence and paragraph lengths**
- **SEO-friendly meta data**

## 📊 Monitoring & Logs

Access detailed logs at **Tools > ArticleCraft AI Logs** to track:

- Processing history
- Token usage per operation
- Processing times
- Source URLs
- Generated content previews

## 🛠️ Troubleshooting

### Common Issues

#### "Can't locate ArticleCraft AI in admin"
**Menu Locations:**
- Main settings: `Settings > ArticleCraft AI`
- Logs: `Tools > ArticleCraft AI Logs`
- Post editor: Look for **ArticleCraft AI Generator** meta box in post/page editor

#### "API Error" Messages
1. Verify your API key is correct
2. Check your API provider account has sufficient credits
3. Ensure the selected model is available for your account

#### "Unable to scrape URL"
1. Check if the URL is accessible
2. Some sites block automated scraping
3. Try a different URL or contact site owner

#### "No content generated"
1. Verify API settings are correct
2. Check if you have sufficient API credits
3. Try reducing max tokens setting

### Getting Help

1. Check the logs at **Tools > ArticleCraft AI Logs** for error details
2. Verify your API configuration in **Settings > ArticleCraft AI**
3. Ensure your WordPress meets minimum requirements

## 🔒 Security & Privacy

- API keys are stored securely in WordPress database
- All communications with AI providers use HTTPS
- No content is stored permanently by AI providers
- Content processing is logged locally for monitoring

## 🌟 Best Practices

### For URL Generation
- Use high-quality source URLs with substantial content
- Verify source credibility before generating
- Review and fact-check generated content

### For Content Rewriting
- Provide clear, well-structured original content
- Review AI suggestions before publishing
- Maintain your unique voice and perspective

### API Usage Optimization
- Monitor token usage in logs
- Adjust max tokens based on content needs
- Use appropriate temperature settings (0.7 recommended)

## 📈 Supported Content Types

- News articles
- Blog posts
- Technical documentation
- Product reviews
- Industry analysis
- How-to guides
- Opinion pieces

## 🔄 Version History

### v1.0.0 (Current)
- Initial release
- URL-to-article generation
- Content rewriting functionality
- Multi-provider AI support
- Gutenberg integration
- Activity logging

## 📞 Support

For support and updates, visit:
- Plugin Repository: [GitHub](https://github.com/cyberkarhub/articlecraft-ai)
- Issues: [GitHub Issues](https://github.com/cyberkarhub/articlecraft-ai/issues)

## 📄 License

This plugin is licensed under the GPL v2 or later.

---

**Made with ❤️ by CyberKarHub**