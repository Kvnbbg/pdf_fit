import subprocess
import sys
import webbrowser
import time
import os

def install_dependencies():
    print("Welcome to PDF Size Reducer!")
    print("This tool will help you compress PDF files.")

    try:
        # Check if pip is available
        subprocess.run([sys.executable, '-m', 'pip', '--version'], check=True, stdout=subprocess.PIPE, stderr=subprocess.PIPE)
    except FileNotFoundError:
        print("Error: pip not found. Please ensure Python is installed correctly.")
        sys.exit(1)
    except subprocess.CalledProcessError as e:
        print("Error: Pip is not working properly.")
        print(f"Error Details: {e}")
        sys.exit(1)

    try:
        # Check if PyPDF2 library is installed
        subprocess.run([sys.executable, '-m', 'pip', 'show', 'PyPDF2'], check=True, stdout=subprocess.PIPE, stderr=subprocess.PIPE)
        print("Dependencies already installed.")
    except subprocess.CalledProcessError:
        print("Installing dependencies...")
        try:
            subprocess.run([sys.executable, '-m', 'pip', 'install', 'PyPDF2'], check=True)
            print("Dependencies installed successfully!")
        except subprocess.CalledProcessError as e:
            print("Error: Failed to install dependencies.")
            print(f"Error Details: {e}")
            sys.exit(1)

    input("Press Enter to continue...")  # Wait for user confirmation

def run_pdf_size_reducer():
    # Clear the console after the delay
    time.sleep(2)
    if os.name == 'nt':
        os.system('cls')
    else:
        os.system('clear')

    print("Launching PDF Size Reducer...")
    subprocess.run([sys.executable, 'pdf_size_reduce.py'], check=True)

def show_thanks_message():
    print("\nThank you for using PDF Size Reducer!")
    print("Your feedback is valuable. If you'd like to contribute or report issues, visit:")
    print("https://github.com/kvnbbg")
    webbrowser.open("https://github.com/kvnbbg")

def main():
    install_dependencies()
    run_pdf_size_reducer()
    show_thanks_message()

if __name__ == "__main__":
    main()
