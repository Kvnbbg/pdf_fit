import subprocess

# List of required dependencies
dependencies = ['PyPDF2']

def check_installation(package_name):
    try:
        subprocess.run(['pip', 'show', package_name], check=True, stdout=subprocess.PIPE, stderr=subprocess.PIPE)
        return True
    except subprocess.CalledProcessError:
        return False

def install_dependencies():
    for dependency in dependencies:
        if not check_installation(dependency):
            print(f"Installing {dependency}...")
            subprocess.run(['pip', 'install', dependency], check=True)
            print(f"{dependency} installed successfully!")

def main():
    print("Checking dependencies...")
    install_dependencies()
    print("All dependencies installed.")

if __name__ == "__main__":
    main()
