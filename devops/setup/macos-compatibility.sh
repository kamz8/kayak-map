#!/bin/bash

# Kolory dla output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}🍎 Sprawdzanie kompatybilności macOS${NC}"
echo "===================================="

# Wykryj system operacyjny
OS="$(uname -s)"
case "${OS}" in
    Darwin*)    MACHINE="Mac";;
    Linux*)     MACHINE="Linux";;
    CYGWIN*)    MACHINE="Cygwin";;
    MINGW*)     MACHINE="MinGw";;
    *)          MACHINE="UNKNOWN:${OS}"
esac

echo -e "🖥️  Wykryty system: ${YELLOW}${MACHINE}${NC}"

if [ "$MACHINE" != "Mac" ]; then
    echo -e "${GREEN}✅ System nie jest macOS - standardowe skrypty powinny działać${NC}"
    exit 0
fi

echo -e "${YELLOW}📋 Sprawdzanie wymagań dla macOS...${NC}"

# Sprawdź czy to Apple Silicon czy Intel
if [[ $(uname -m) == 'arm64' ]]; then
    echo -e "💻 Architektura: ${BLUE}Apple Silicon (M1/M2/M3)${NC}"
    ARCH="arm64"
else
    echo -e "💻 Architektura: ${BLUE}Intel${NC}"
    ARCH="x86_64"
fi

# Sprawdź Homebrew
if command -v brew >/dev/null 2>&1; then
    echo -e "${GREEN}✅ Homebrew zainstalowany${NC}"
    BREW_VERSION=$(brew --version | head -n1)
    echo -e "   Wersja: ${BREW_VERSION}"
else
    echo -e "${RED}❌ Homebrew nie jest zainstalowany${NC}"
    echo -e "${YELLOW}💡 Instalacja Homebrew:${NC}"
    echo '/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"'
fi

# Sprawdź Docker Desktop
if command -v docker >/dev/null 2>&1; then
    echo -e "${GREEN}✅ Docker zainstalowany${NC}"
    DOCKER_VERSION=$(docker --version)
    echo -e "   ${DOCKER_VERSION}"
    
    # Sprawdź czy Docker działa
    if docker info >/dev/null 2>&1; then
        echo -e "${GREEN}✅ Docker daemon działa${NC}"
    else
        echo -e "${YELLOW}⚠️  Docker daemon nie działa - uruchom Docker Desktop${NC}"
    fi
else
    echo -e "${RED}❌ Docker nie jest zainstalowany${NC}"
    echo -e "${YELLOW}💡 Instalacja Docker Desktop dla Mac:${NC}"
    if [ "$ARCH" == "arm64" ]; then
        echo "   https://desktop.docker.com/mac/main/arm64/Docker.dmg"
    else
        echo "   https://desktop.docker.com/mac/main/amd64/Docker.dmg"
    fi
fi

# Sprawdź PHP
if command -v php >/dev/null 2>&1; then
    PHP_VERSION=$(php -v | head -n1 | cut -d" " -f2 | cut -d"." -f1,2)
    if [[ $(echo "$PHP_VERSION >= 8.2" | bc -l) -eq 1 ]]; then
        echo -e "${GREEN}✅ PHP ${PHP_VERSION} (OK)${NC}"
    else
        echo -e "${YELLOW}⚠️  PHP ${PHP_VERSION} (wymagane 8.2+)${NC}"
        echo -e "${YELLOW}💡 Aktualizacja PHP przez Homebrew:${NC}"
        echo "   brew install php@8.2"
    fi
else
    echo -e "${RED}❌ PHP nie jest zainstalowany${NC}"
    echo -e "${YELLOW}💡 Instalacja PHP:${NC}"
    echo "   brew install php@8.2"
fi

# Sprawdź Composer
if command -v composer >/dev/null 2>&1; then
    COMPOSER_VERSION=$(composer --version | head -n1 | cut -d" " -f3)
    echo -e "${GREEN}✅ Composer ${COMPOSER_VERSION}${NC}"
else
    echo -e "${RED}❌ Composer nie jest zainstalowany${NC}"
    echo -e "${YELLOW}💡 Instalacja Composer:${NC}"
    echo "   brew install composer"
fi

# Sprawdź Node.js
if command -v node >/dev/null 2>&1; then
    NODE_VERSION=$(node -v)
    echo -e "${GREEN}✅ Node.js ${NODE_VERSION}${NC}"
else
    echo -e "${RED}❌ Node.js nie jest zainstalowany${NC}"
    echo -e "${YELLOW}💡 Instalacja Node.js:${NC}"
    echo "   brew install node"
fi

# Sprawdź NPM
if command -v npm >/dev/null 2>&1; then
    NPM_VERSION=$(npm -v)
    echo -e "${GREEN}✅ NPM ${NPM_VERSION}${NC}"
else
    echo -e "${RED}❌ NPM nie jest zainstalowany${NC}"
fi

# Sprawdź OpenSSL (ważne dla szyfrowania)
if command -v openssl >/dev/null 2>&1; then
    OPENSSL_VERSION=$(openssl version)
    echo -e "${GREEN}✅ OpenSSL zainstalowany${NC}"
    echo -e "   ${OPENSSL_VERSION}"
    
    # Sprawdź czy to LibreSSL (domyślny w macOS) czy OpenSSL
    if openssl version | grep -q "LibreSSL"; then
        echo -e "${YELLOW}⚠️  Używasz LibreSSL (domyślny macOS)${NC}"
        echo -e "${YELLOW}💡 Dla pełnej kompatybilności zainstaluj OpenSSL:${NC}"
        echo "   brew install openssl"
        echo "   export PATH=\"/opt/homebrew/opt/openssl@3/bin:\$PATH\""
    fi
else
    echo -e "${RED}❌ OpenSSL nie jest zainstalowany${NC}"
fi

# Sprawdź Make
if command -v make >/dev/null 2>&1; then
    MAKE_VERSION=$(make --version | head -n1)
    echo -e "${GREEN}✅ Make zainstalowany${NC}"
    echo -e "   ${MAKE_VERSION}"
else
    echo -e "${RED}❌ Make nie jest zainstalowany${NC}"
    echo -e "${YELLOW}💡 Instalacja Xcode Command Line Tools:${NC}"
    echo "   xcode-select --install"
fi

# Sprawdź Git
if command -v git >/dev/null 2>&1; then
    GIT_VERSION=$(git --version)
    echo -e "${GREEN}✅ Git zainstalowany${NC}"
    echo -e "   ${GIT_VERSION}"
else
    echo -e "${RED}❌ Git nie jest zainstalowany${NC}"
fi

echo ""
echo -e "${BLUE}📋 Podsumowanie dla macOS:${NC}"
echo "=========================="

if [ "$MACHINE" == "Mac" ]; then
    echo -e "${GREEN}✅ System macOS wykryty${NC}"
    echo -e "${GREEN}✅ Skrypty bash powinny działać${NC}"
    echo -e "${GREEN}✅ Docker Compose jest obsługiwany${NC}"
    echo ""
    echo -e "${YELLOW}🔧 Potencjalne różnice macOS:${NC}"
    echo "• date command: użycie BSD date (może wymagać dostosowania)"
    echo "• sed command: domyślnie BSD sed (nie GNU sed)"
    echo "• Uprawnienia plików: system inny niż Linux"
    echo ""
    echo -e "${YELLOW}💡 Zalecenia dla macOS:${NC}"
    echo "• Użyj chmod +x devops/**/*.sh do uprawnień"
    echo "• Docker Desktop musi być uruchomiony"
    echo "• Rozważ instalację GNU tools przez Homebrew"
fi

echo ""
echo -e "${GREEN}🎉 Sprawdzanie zakończone!${NC}"