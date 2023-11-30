import tkinter as tk
from tkinter import filedialog
import PyPDF2
import traceback
import os

# Function to reduce PDF size
def reduce_pdf_size():
    try:
        # Select the PDF file to compress
        input_file_path = filedialog.askopenfilename(title="Select PDF file to compress")
        if not input_file_path:
            result_label.config(text="No file selected. Operation canceled.")
            return

        # Choose where to save the compressed PDF
        output_file_path = filedialog.asksaveasfilename(
            title="Save the compressed PDF", defaultextension=".pdf"
        )
        if not output_file_path:
            result_label.config(text="Operation canceled.")
            return

        # Check if output path already exists
        if os.path.exists(output_file_path):
            result_label.config(text="Error: File already exists. Choose a different name.")
            return

        with open(input_file_path, 'rb') as file:
            pdf_reader = PyPDF2.PdfReader(file)
            pdf_writer = PyPDF2.PdfWriter()

            for page_num in range(pdf_reader.numPages):
                page = pdf_reader.getPage(page_num)
                page.compressContentStreams()
                pdf_writer.addPage(page)

            # Save the reduced PDF
            with open(output_file_path, 'wb') as output_file:
                pdf_writer.write(output_file)
                result_label.config(text="PDF size reduced successfully!")

                # Check the size of the new PDF
                new_pdf_size = os.path.getsize(output_file_path)
                if new_pdf_size < 2000000:  # 2 MB in bytes
                    result_label.config(
                        text=f"PDF size reduced successfully! New file size: {round(new_pdf_size / 1024, 2)} KB"
                    )
                else:
                    result_label.config(
                        text="The reduced PDF is still larger than 2MB. Consider other compression methods."
                    )

    except PyPDF2.utils.PdfReadError:
        result_label.config(text="Error: Invalid or corrupted PDF file.")
    except Exception as e:
        result_label.config(text="An error occurred during processing.")
        print(f"Error: {e}")
        traceback.print_exc()

# Create the GUI
root = tk.Tk()
root.title("PDF Size Reducer")

# Beautiful typography and styles
root.option_add("*Font", "Helvetica 12")  # Change font and size
root.option_add("*Button.Background", "lightblue")  # Change button background color
root.option_add("*Button.Foreground", "black")  # Change button text color

# Reduce PDF Size Button
reduce_button = tk.Button(root, text="Reduce PDF Size", command=reduce_pdf_size)
reduce_button.pack(pady=20)

# Retry Button (hidden initially)
retry_button = tk.Button(root, text="Retry", command=reduce_pdf_size)
retry_button.pack(pady=10)
retry_button.pack_forget()  # Hide the button initially

# Display Result Label
result_label = tk.Label(root, text="")
result_label.pack()

# Instructions for the user
instruction_label = tk.Label(
    root,
    text="Thank you for using this app! If you liked it, consider supporting Code Newbie by liking the code or leaving a message on YouTube.\n- Developed by kvnbbg (GitHub) a.k.a Kevin Marville.",
    wraplength=300,
)
instruction_label.pack()

# Start GUI event loop
root.mainloop()
