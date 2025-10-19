#!/bin/bash
set -e

# Ensure HOME is set
HOME_DIR="${HOME:-/home/dev}"

# Install Laravel installer globally if not installed
if ! composer global show laravel/installer >/dev/null 2>&1; then
    echo "🚀 Installing Laravel installer globally..."
    composer global require laravel/installer
else
    echo "✅ Laravel installer already installed globally."
fi

# Add Laravel installer to PATH for interactive shells
if ! grep -q 'export PATH="$HOME/.composer/vendor/bin:$PATH"' "$HOME_DIR/.bashrc"; then
    echo 'export PATH="$HOME/.composer/vendor/bin:$PATH"' >> "$HOME_DIR/.bashrc"
    echo "✅ PATH added to ~/.bashrc"
fi

echo "⚡ Restart your shell or run 'source ~/.bashrc' to use 'laravel' command"
