import { Outlet } from "react-router-dom"
import Navbar from "../components/Navbar"

function NavbarLayout() {
  return (
<>
<Navbar />
<Outlet />
</>
)

}

export default NavbarLayout