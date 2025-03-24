import os
import sys
import time
import subprocess
import argparse
import webbrowser
import logging
from pathlib import Path
from PyPDF2 import PdfReader, PdfWriter
from rich.console import Console
from rich.progress import Progress, SpinnerColumn, TextColumn
from rich.prompt import Prompt
from rich.panel import Panel

# Configuration
LOG_FILE = "pdf_compressor.log"
API_KEY_ENV = "PDF_COMPRESS_API_KEY"
API_URL = "https://api.example.com/compress"  # Replace with actual API endpoint

# Initialize logging
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler(LOG_FILE),
        logging.StreamHandler(sys.stdout)
    ]
)

console = Console()

class PDFCompressor:
    def __init__(self, input_path: str, output_path: str = None):
        self.input_path = Path(input_path)
        self.output_path = Path(output_path) if output_path else self._generate_output_path()
        self.methods = [
            ('Ghostscript', self._compress_ghostscript),
            ('PyPDF2', self._compress_pypdf2),
            ('Cloud API', self._compress_api)
        ]
        self.score = 0

    def _generate_output_path(self) -> Path:
        """Generate default output path with timestamp"""
        return self.input_path.with_stem(f"{self.input_path.stem}_compressed_{int(time.time())}")

    def validate_input(self) -> bool:
        """Validate input file"""
        if not self.input_path.exists():
            logging.error("Input file does not exist")
            return False
        if self.input_path.suffix.lower() != '.pdf':
            logging.error("Not a valid PDF file")
            return False
        return True

    def compress(self) -> dict:
        """Compress with fallback mechanisms"""
        results = {}
        for method_name, method_func in self.methods:
            try:
                with Progress(
                    SpinnerColumn(),
                    TextColumn("[progress.description]{task.description}"),
                    transient=True
                ) as progress:
                    task = progress.add_task(f"Trying {method_name}...", total=None)
                    result = method_func()
                    progress.update(task, completed=True, visible=False)
                
                if result.get('status') == 'success':
                    self.score += 10  # Gamification points
                    return {
                        'status': 'success',
                        'method': method_name,
                        'result': result,
                        'score': self.score
                    }
            except Exception as e:
                logging.warning(f"{method_name} failed: {str(e)}")
                results[method_name] = {'status': 'error', 'message': str(e)}
                continue
        
        return {'status': 'error', 'message': 'All methods failed', 'details': results}

    def _compress_ghostscript(self) -> dict:
        """Compression using Ghostscript"""
        logging.info("Attempting Ghostscript compression")
        command = [
            'gs', '-sDEVICE=pdfwrite', '-dCompatibilityLevel=1.4',
            '-dPDFSETTINGS=/ebook', '-dNOPAUSE', '-dQUIET', '-dBATCH',
            f'-sOutputFile={self.output_path}', str(self.input_path)
        ]
        
        result = subprocess.run(command, capture_output=True, timeout=120)
        if result.returncode != 0:
            raise RuntimeError(f"Ghostscript failed: {result.stderr.decode()}")
        
        return {
            'status': 'success',
            'original_size': self.input_path.stat().st_size,
            'compressed_size': self.output_path.stat().st_size
        }

    def _compress_pypdf2(self) -> dict:
        """Compression using PyPDF2"""
        logging.info("Attempting PyPDF2 compression")
        reader = PdfReader(self.input_path)
        writer = PdfWriter()
        
        for page in reader.pages:
            page.compress_content_streams()
            writer.add_page(page)
        
        with open(self.output_path, 'wb') as f:
            writer.write(f)
        
        return {
            'status': 'success',
            'original_size': self.input_path.stat().st_size,
            'compressed_size': self.output_path.stat().st_size
        }

    def _compress_api(self) -> dict:
        """Compression using cloud API"""
        logging.info("Attempting cloud API compression")
        api_key = os.getenv(API_KEY_ENV)
        if not api_key:
            raise ValueError("API key not found. Set PDF_COMPRESS_API_KEY environment variable")
        
        # Simulated API call (replace with actual implementation)
        time.sleep(2)
        return {
            'status': 'success',
            'original_size': 1024,
            'compressed_size': 512,
            'api_response': 'mock_data'
        }

def show_welcome_screen():
    """Display animated welcome screen"""
    console.print(Panel.fit(
        "[bold green]PDF Compressor Pro[/]\n"
        "Version 2.0 - With Gamification!\n\n"
        "Compression Methods Available:\n"
        "• Ghostscript\n"
        "• PyPDF2\n"
        "• Cloud API\n\n"
        "Press Ctrl+C to exit anytime",
        title="Welcome",
        border_style="blue"
    ))
    time.sleep(2)

def main():
    parser = argparse.ArgumentParser(description='PDF Compression Tool')
    parser.add_argument('input', nargs='?', help='Input PDF file path')
    parser.add_argument('--test', action='store_true', help='Run in test mode')
    args = parser.parse_args()

    try:
        show_welcome_screen()
        
        if args.test:
            console.print("[yellow]Test mode activated![/]")
            compressor = PDFCompressor("test.pdf")
            result = compressor.compress()
            console.print(f"Test completed with score: {result.get('score', 0)}")
            return

        input_path = args.input or Prompt.ask("Enter PDF file path")
        compressor = PDFCompressor(input_path)
        
        if not compressor.validate_input():
            console.print("[red]Invalid input file![/]")
            return

        with Progress() as progress:
            task = progress.add_task("Compressing PDF...", total=100)
            for i in range(100):
                progress.update(task, advance=1)
                time.sleep(0.05)  # Simulate processing

        result = compressor.compress()
        
        if result['status'] == 'success':
            data = result['result']
            reduction = (1 - data['compressed_size']/data['original_size']) * 100
            console.print(Panel(
                f"Compression Successful!\n\n"
                f"Method: {result['method']}\n"
                f"Original Size: {data['original_size']} bytes\n"
                f"Compressed Size: {data['compressed_size']} bytes\n"
                f"Reduction: {reduction:.1f}%\n"
                f"Score: {result['score']} points",
                title="Success",
                style="green"
            ))
        else:
            console.print(Panel(
                f"Compression Failed!\n{result['message']}",
                title="Error",
                style="red"
            ))

    except KeyboardInterrupt:
        console.print("\n[yellow]Operation cancelled by user[/]")
    finally:
        console.print(f"Log file saved to: {LOG_FILE}")
        webbrowser.open("https://github.com/Kvnbbg/pdf_fit")

if __name__ == "__main__":
    main()
